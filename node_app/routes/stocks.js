const express = require('express');
const router = express.Router();

const stockLineupCtrl = require('../controllers/stockLineupCtrl')
const stockPredictCtrl = require('../controllers/stockPredictCtrl')

router.get('/lineup_move',stockLineupCtrl.lineup_move)//common lib done
router.get('/predict/lineup_move',stockPredictCtrl.lineup_move)//common lib done

module.exports.router = router;