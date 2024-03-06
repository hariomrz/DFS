import React, { useEffect, useRef, useState } from "react";
import 'react-tabs-scrollable/dist/rts.css'
import Slider from "react-slick";
import { Utilities, _Map, _chunk, _debounce, _filter, _isEmpty, getPropsName, _reduce } from "../../../../Utilities/Utilities";
import { CommonLabels } from "../../../../helper/AppLabels";
import { MomentDateComponent } from '../../../CustomComponent';
import * as AppLabels from "../../../../helper/AppLabels";
import WSManager from '../../../../WSHelper/WSManager';
import * as WSC from "../../../../WSHelper/WSConstants";
import { BarChart, Bar, Cell, XAxis, YAxis, CartesianGrid, ResponsiveContainer, ReferenceLine } from "recharts";
import PropsEntryStripe from "../PropsEntryStripe/PropsEntryStripe";

const API = {
    GET_PLAYER_CARD: WSC.propsURL + "props/lobby/get_player_card_stats"
}

const PropsPlayerCard = ({
    seletcedProps = [],
    list = [],
    item = {},
    player_idx = 0,
    player_season_id = null,
    tournament_type = null,
    onHide = () => { },
    handleChecked = () => { },
    PropsEntryStripeData = {},
    hideStrip = false,
    ...props }) => {

    const sliderRef = useRef();
    const settings = {
        touchThreshold: 10,
        infinite: false,
        slidesToScroll: 1,
        slidesToShow: 1,
        initialSlide: 0,
        centerPadding: '15px',
        dots: false,
        autoplay: false,
        centerMode: true,
        variableWidth: true,
        // initialSlide: 1,
        responsive: [
            {
                breakpoint: 480,
                settings: {
                    // variableWidth: false
                }
            },
        ]
    };
    const perChunk = 3;
    const [init, setInit] = useState(false);
    const [chunkArr, setChunkArr] = useState([]);
    const [currentChunkArr, setCurrentChunkArr] = useState([]);
    const [chunkIndex, setChunkIdx] = useState(0);
    const [playerIdx, setPlayerIdx] = useState(player_idx)
    const [playerCard, setPlayerCard] = useState({
        [player_season_id]: item,
        [tournament_type]: item,
    })
 
    
    const getPlayerCard = (season_prop_id, tournament_value, bool = true) => {
        if (playerCard[season_prop_id] && bool) return;
        WSManager.Rest(API.GET_PLAYER_CARD, { 'season_prop_id': season_prop_id , "tournament_type":tournament_value }).then(({ response_code, data, ...res }) => {
            if (response_code == WSC.successCode) {
                setPlayerCard((prev) => ({
                    ...prev, [season_prop_id]: data
                }))
            }
        });
    }

    const isActive = (item) => {
        // e.stopPropagation()
        return _filter(seletcedProps, obj => obj.season_prop_id == item.season_prop_id).length == 1 ? 'active' : ''
    }

    const afterChange = (idx) => {
        onHandleChange(idx)
        if (idx == currentChunkArr.length) {
            setChunkIdx((prev => (prev + 1)))
            if (chunkArr[chunkIndex + 1]) {
                setCurrentChunkArr((prev) => ([...prev, ...chunkArr[chunkIndex + 1] ]))
            }
        }
        
    }
    const beforeChange = (idx) => {
        // const parentIdx = list.findIndex((obj) => obj.season_prop_id === currentChunkArr[idx].season_prop_id);
        // let _idx = Math.floor(parentIdx / perChunk)
        // if (idx == 1) {
        //     setChunkIdx(_idx - 1)
        //     setCurrentChunkArr([...(chunkArr[_idx - 1] || []), ...currentChunkArr])
        //     // console.log(perChunk + idx);
        //     // sliderRef.current.slickGoTo(perChunk + idx);
        // }
    }

    const onHandleChange = _debounce((idx) => {
        if (!idx) return;
        const _item = currentChunkArr[idx - 1];
        // getPlayerCard(_item.season_prop_id)
        getPlayerCard(_item.season_prop_id, _item.tournament_type)
    }, 300)

    useEffect(() => {
        // getPlayerCard(player_season_id, false)
        getPlayerCard(player_season_id, tournament_type, false)
        return () => { }
    }, [])

    useEffect(() => {
        setChunkArr(_chunk(list, perChunk))
        return () => { }
    }, [list])

    useEffect(() => {
        setInit(true)
        let _idx = Math.floor(player_idx / perChunk)
        let _chunkArr = chunkArr[_idx]

        setChunkIdx(_idx)
        setCurrentChunkArr(_chunkArr)
        if (_chunkArr) {
            const slideIdx = _chunkArr.findIndex((obj) => obj.season_prop_id === player_season_id);

            if ((slideIdx + 1) % perChunk === 0) {
                if(chunkArr[_idx + 1]) {
                    setCurrentChunkArr((prev) => ([...prev, ...chunkArr[_idx + 1]]))
                }
                sliderRef.current.slickGoTo(slideIdx);
            }
            if ((slideIdx + 2) % perChunk === 0) {
                sliderRef.current.slickGoTo(slideIdx);
            }
            if (slideIdx % 4 === 0 && _idx != 0) {
                setCurrentChunkArr((prev) => ([
                    ...(chunkArr[_idx - 1] || [])
                    , ...prev]))
                sliderRef.current.slickGoTo(perChunk + slideIdx);
            }
        }
        return () => { }
    }, [chunkArr])

    return (
        <>
            <div className="player-card-backdrop show" />
            <div className="player-card-wrap">
                <i className="icon-close" onClick={onHide} />
                <div onClick={(e) => e.stopPropagation()}>
                    {
                        init &&
                        <Slider 
                            {...settings} 
                            afterChange={(e) => afterChange(e + 1)} 
                            beforeChange={(e) => beforeChange(e)} 
                            ref={sliderRef} 
                        >
                            {/* <div className="player-card-modal" >
                                    <span>Blank</span>
                                </div> */}
                            {
                                _Map(currentChunkArr, (item, idx) => {
                                    return (
                                        <div className="player-card-modal" key={idx}>
                                            <PlayerCardSlide {...{
                                                item: { ...item, ...(!_isEmpty(playerCard) ? playerCard[item.season_prop_id] : {}) },
                                                handleChecked,
                                                isActive,
                                                posting: playerCard[item.season_prop_id] ,
                                                propsList: props.propsList,
                                                hideStrip
                                            }} />
                                        </div>
                                    )
                                })
                            }
                        </Slider>
                    }
                </div>

                {(!_isEmpty(seletcedProps) && hideStrip == false) && <PropsEntryStripe {...PropsEntryStripeData} />}
            </div>
        </>
    )
}

export default PropsPlayerCard



const PlayerCardSlide = ({ item = {}, handleChecked, isActive, posting, propsList, hideStrip }) => {

    const [chartData, setChartData] = useState([]);


    let team_name = item.team_id == item.away_id ? item.away : item.home
    let opp_name = item.team_id == item.away_id ? item.home : item.away
    let jersey = item.team_id == item.away_id ? item.away_jersey : item.home_jersey

    const getAverage = (data) => {
        const sumOfScores = data.reduce((sum, obj) => sum + parseInt(obj.score), 0);
        return (sumOfScores / data.length).toFixed(1);

    }
    useEffect(() => {
        const _chartData = _Map(item.stats, obj => {
            return {
                ...obj, score: Number(obj.score)
            }
        })

        setChartData(_chartData)

    return () => {}
    }, [item])


    return (
        <div className="props-player-web-container">

            <div
                className={`props-player-card`}
            >
                <div className="d-flex">
                    <img src={Utilities.playerJersyURL(item.player_image != "" ? item.player_image : jersey)} className='jersey' />
                    <div className='j-block ml-2'>
                        <h6 className='name'>{item.full_name}</h6>
                        <h6 className='match'>{team_name} - {item.position}</h6>
                        <h6 className='timing'>{Utilities.getFormatedDateTime(item.scheduled_date, 'ddd, MMM DD hh:mm A ')} vs {opp_name}</h6>
                        <div className='score'>
                            <div className='points'>{item.points}</div>
                            <div className='divide'>|</div>
                            <div className='runs'>
                                {getPropsName(propsList, item.prop_id)}
                                {/* {item.prop_id == "1" ? 'Runs' : item.prop_id == "2" ? CommonLabels.WICKETS : item.prop_id == "3" ? CommonLabels.SIXES : CommonLabels.FOURS} */}
                            </div>
                        </div>
                    </div>
                </div>
                <div className='jersey-header'>
                    {
                        !hideStrip &&
                        <div className={`check ${isActive(item)}`} onClick={() => handleChecked(item)}>
                            <i className='icon-tick-ic'></i>
                        </div>
                    }
                    {/* <div class="unstyled centered">
                        <input class="styled-checkbox"/>
                        <label for={`styled-checkbox`} onClick={() => handleChecked(item)}></label>
                    </div> */}
                </div>
            </div>
            <div className="props-lower-card">
                {
                    posting && _isEmpty(item.stats) ?
                    <div className="no-data-container">
                        <h3>{AppLabels.NO_DATA_FOUND}</h3>
                    </div>
                    :
                    <>
                        {
                            posting ?
                                <>
                                    {
                                        !_isEmpty(chartData) &&
                                        <div className="props-graph">
                                            <BarChart width={270} height={120} data={chartData} margin={{
                                                    top: 20,
                                                    right: 50,
                                                    left: -40,
                                                    bottom: 0
                                                }}>
                                                <CartesianGrid strokeDasharray="3 3" />
                                                <XAxis
                                                    tick={false}
                                                    dataKey="score"
                                                    axisLine={{color: '#D8D8D8'}}
                                                    tickSize={1}
                                                />
                                                <YAxis 
                                                    axisLine={{color: '#D8D8D8'}}
                                                    tickSize={1}/>
                                                    <ReferenceLine strokeDasharray="5 5" y={item.points} stroke={'#D8D8D8'} label={<ReferenceLabel value={`${item.points}`}  />} />


                                                    {/* <ReferenceLine
                                                        y={item.points}
                                                        label={<CustomLabel text={`Proj.${' '}${item.points}`}/>}
                                                        // label={{
                                                        //     textBreakAll:true,
                                                        //     fontSize: "14px",
                                                        //     position: "right",
                                                        //     value: `Proj.${' '}${item.points}`,
                                                        //     className: 'project-referenceline'
                                                        // }}
                                                        strokeDasharray="5 5"
                                                    /> */}
                                                <Bar dataKey="score">
                                                    {chartData.map((entry, index) => (
                                                        <Cell
                                                            radius={[100, 100, 0, 0]}
                                                            width={17}
                                                            cursor="pointer"
                                                            fill={entry.score > item.points ? '#0DAE6F' : '#D84646'}
                                                            key={`cell-${index}`}
                                                        />
                                                    ))}
                                                </Bar>
                                            </BarChart>
                                        </div>
                                    }
                                    {
                                        item.stats &&
                                        <div className="props-avg">
                                            <div className="avg-text">
                                                {CommonLabels.AVG_LAST} {item.stats.length}
                                            </div>
                                            <div className="avg-points">
                                                {
                                                getAverage(item.stats)
                                                }
                                            </div>
                                        </div>
                                    }
                                    {item.stats && item.stats.length > 0 && <div className="props-table">
                                        <div className="heading d-flex">
                                            <p className="day">{AppLabels.DAY}</p>
                                            <p className="opp">{CommonLabels.OPP}</p>
                                            <p className="runs">{getPropsName(propsList, item.prop_id)}</p>
                                        </div>
                                        {item.stats && item.stats.map((stats) => {
                                            return (
                                                <div className="body d-flex">
                                                    <p className="day">
                                                        <MomentDateComponent data={{ date: stats.match_date, format: "MMM D" }} />
                                                    </p>
                                                    <p className="opp">{stats.away}</p>
                                                    <p className="runs">{stats.score}</p>
                                                </div>
                                            )
                                        })
                                        }
        
                                        {
                                            _isEmpty(item.stats) &&
                                            <div className="no-data-playercard">
                                                No data
                                            </div>
                                        }
                                    </div>}
                                </>
                                :
                                <>
                                    <div className="plloader" />
                                </>
                        }
                    </>
                }
            </div>
        </div>
    )
}


const CustomLabel = props => {

  return (
    <g>
      <rect
        x={props.viewBox.x}
        y={props.viewBox.y}
        width={30}
        height={35}
      />
      <text x={props.viewBox.x} y={props.viewBox.y} fill="#333" dy={20} dx={30}>
        {props.text}
      </text>
    </g>
  );
};



const ReferenceLabel = (props) => {
    const {
        fill, value, textAnchor,
        fontSize, viewBox, dy, dx,
    } = props;
    const x = viewBox.width + viewBox.x + 10;
    const y = viewBox.y - 17;
    return (
        <foreignObject width="30" height="35"
            x={x} y={y}
            dy={dy}
            dx={dx}
            fill={fill}
            fontSize={fontSize || 12}
            textAnchor={textAnchor}>
            <div className="project-referenceline">
                Proj. <br />{value}
            </div>
        </foreignObject>
    )
}