"use strict";
const util = require('util');
const _ = require('lodash')
const CONSTANTS = require('../constants')
const helper = require('../helper/default_helper')
const stock_conn = require('../db_stock_config')  
const lineupModel  = require('../models/stockLineupModel')
const query = util.promisify(stock_conn.query).bind(stock_conn);
const amqp = require('amqplib/callback_api');
function add_data_to_queue(queue_name,data)
{
	const opt = { credentials: require('amqplib').credentials.plain(process.env.MQ_USER, process.env.MQ_PASSWORD) };
	amqp.connect('amqp://'+process.env.MQ_HOST,opt, function(error, connection) {
		if (error) {
		  throw error;
		}
		connection.createChannel(function(error1, channel) {
		  if (error1) {
			throw error1;
		  }
	  
		  let queue = queue_name;
		  let msg = data;
	  
		  channel.assertQueue(queue, {
			durable: true
		  });
		  channel.sendToQueue(queue, Buffer.from(JSON.stringify(msg)), {
			persistent: true
		  });
		  //console.log("Sent '%s'", msg);
		});
		setTimeout(function() {
		  connection.close();
		  process.exit(0)
		}, 500);
	  });
}

exports.lineup_move = async function(req,res)
{
     var start = new Date();
	var end = 0;
     var collection_master_data= await lineupModel.get_current_live_collection(3);
     
     if(!collection_master_data.length)
     {
          helper.res.message ='No Match available.'
          helper.res.data = [];
          helper.res.response_code =200;
          res.json(helper.res)
          return;
     }
     var collection_master_data = collection_master_data[0];
     console.log("collection_master_data",collection_master_data);
     var lineup_master_data=await lineupModel.get_lineup_master_data(collection_master_data);
     //console.log("lineup_master_data",lineup_master_data);
     
      if(!lineup_master_data.length)
	{    
          try {

             var update_cond = ` is_lineup_processed=1`;
            
             var  rows = await query(`UPDATE  ${CONSTANTS.TABLE_PREFIX+CONSTANTS.COLLECTION} SET ${update_cond} WHERE collection_id=${collection_master_data.collection_id}`);
          }
          catch (e) {
               console.log("entering catch block");
               console.log(e);
               console.log("leaving catch block");
             }

          helper.res.message ='No lineup available.'
          helper.res.data = [];
          helper.res.response_code =200;
          res.json(helper.res)
          return;
     }

     //get players
     var stocks= await lineupModel.get_all_stocks(collection_master_data)

     if(!stocks.length)
     {
          helper.res.message ='No Stock available.'
          helper.res.data = [];
          helper.res.response_code =200;
          res.json(helper.res)
          return;
     }

     var stock_id_arr=helper.array_column_key(stocks,'stock_id');
     var team_chunks = _.chunk(lineup_master_data,499);

     var player_count = await insert_batch_players(collection_master_data,team_chunks,stock_id_arr);

     try{
          var total_stocks = await query(`SELECT COUNT(lineup_id) as stock_counts FROM `+CONSTANTS.TABLE_PREFIX+CONSTANTS.STOCK_LINEUP);
     }
     catch(e)
     {
          console.log("entering catch block");
          console.log(e);
          console.log("leaving catch block");
     }
     

     
     if(total_stocks.length > 0 &&  total_stocks[0].stock_counts >= player_count)
     {
          try{
               var update_lm_cond = ` is_lineup_processed=1`;
               var update_lineup_process = await query(`UPDATE  `+CONSTANTS.TABLE_PREFIX+CONSTANTS.COLLECTION+` SET `+update_lm_cond+` WHERE collection_id=`+collection_master_data.collection_id)

               if(collection_master_data.stock_type === 2)
               {
                    add_data_to_queue("stock_remaining_cap",{collection_id:collection_master_data.collection_id});
               }
          }
          catch(e)
          {
               console.log("entering catch block");
               console.log(e);
               console.log("leaving catch block");
          }
          

          end =new Date() - start;
          console.info('[Execution time]: %ds', end/1000);
          helper.res.message ='Lineup moved. [Execution time]:'+end/1000;

          helper.res.data = [];
          helper.res.response_code =200;
          res.json(helper.res)
          return;
     }
}

async function insert_batch_players(collection_master_data,team_chunks,stock_id_arr)
{
     //get stock type total count
     let stock_type_data = await lineupModel.get_stock_type_data(collection_master_data);

     let tc = 6;
     let config_data = JSON.parse(stock_type_data[0].config_data);
     if(collection_master_data.stock_type ===1)
     {
          tc = config_data.tc;
     }
     else
     {
          tc= config_data.max;
     }

     
     var sql = `INSERT INTO ${CONSTANTS.TABLE_PREFIX+CONSTANTS.STOCK_LINEUP}(lineup_master_id,
          stock_id,
          user_price,
          score,
          captain,
          type,
          user_lot_size,
          added_date) VALUES ?`;


var update_fields = ` ON DUPLICATE KEY UPDATE lineup_master_id =VALUES(lineup_master_id),
                    stock_id=VALUES(stock_id),
                    user_price=VALUES(user_price),
                    score=VALUES(score),
                    captain=VALUES(captain),
                    type=VALUES(type),
                    user_lot_size=VALUES(user_lot_size),
                    added_date=VALUES(added_date)`;	

     var player_count = 0;	
     var lineup_players_count = 0;
     var unique_lineup= [];	
     
     team_chunks.forEach(async (oneChunk) => {
          
          var lineup_team_data = [];
			     _.map(oneChunk,(oneTeam,ti)=>{

					var team_stocks=  JSON.parse(oneTeam.team_data);

                         let stocks_length = team_stocks.stocks.length;
                         if(typeof team_stocks.stocks === 'object')
                         {
                            stocks_length = Object.keys(team_stocks.stocks).length;
                         }

                        
				 	if(team_stocks.stocks && stocks_length>0)
				 	{
						lineup_players_count = 0;
                              
				 		_.map(team_stocks.stocks,(user_price,sid)=>{
                                   console.log('tc',tc,user_price,sid);
				 			if(lineup_players_count<= tc)
				 			{

                                      
								lineup_team_data.push([
								oneTeam.lineup_master_id,
								sid,
                                user_price,
						        0,
								0,
                                0,
                                0,
								helper.format_date()
								]);
				 				lineup_players_count++;
							}
				 		});
					}

                        
			     });

          try {
               console.log('sql',sql);
               console.log('lineup_team_data',lineup_team_data);
               var  rows = await query(sql+` `+update_fields, [lineup_team_data]);
               player_count+=lineup_team_data.length;
               }
          finally{
                    //conn.end();
          }

        });

        return player_count;
}