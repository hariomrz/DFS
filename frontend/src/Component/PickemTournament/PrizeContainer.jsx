import React, { useEffect, useState } from 'react';
import Images from '../../components/images';
import { Utilities, _isEmpty } from '../../Utilities/Utilities';

const PrizeContainer = ({ item }) => {
    const { amount, bonus, coin, merchandise } = item
    const [prizeDetail, setPrizeDetail] = useState([])
    const [prizeData, setPrizeData] = useState({})
    useEffect(() => {
        try {
            setPrizeDetail(JSON.parse(item.prize_detail))
        }
        catch {
            setPrizeDetail(item.prize_detail)
        }
        return () => { }
    }, [item])

    useEffect(() => {
        if (!_isEmpty(prizeDetail)) {
            switch (true) {
                case (item.status == '3' && item.is_winner == '0'):
                    setPrizeData({ ...prizeDetail[0], ...(prizeDetail[0].prize_type == 3 ? {} : { amount: '0' }) })
                    break;
                default:
                    setPrizeData(prizeDetail[0])
                    break;
            }
        }
        return () => { }
    }, [prizeDetail])


    return (
        (item.is_winner == '1' && item.status == 3) ?
            <>
                {' '}
                {
                    amount > 0 ?
                        <>{Utilities.getMasterData().currency_code}{' '}{amount}</>
                        :
                        bonus > 0 ?
                            <><i style={{ display: 'inlineBlock' }} className="icon-bonus" />{' '}{bonus}</>
                            :
                            coin > 0 ?
                                <><img style={{ marginTop: "0px" }} src={Images.IC_COIN} width="15px" height="15px" />{' '}{coin}</>
                                :
                                merchandise != "" ?
                                    'merchandise'
                                    :
                                    <>--</>
                }
            </>
            :
            <>{' '}
                {prizeData.prize_type == 0 && <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                {prizeData.prize_type == 1 && Utilities.getMasterData().currency_code}
                {prizeData.prize_type == 2 && <img style={{ marginTop: "0px" }} src={Images.IC_COIN} width="15px" height="15px" />}
                {' '}{prizeData.amount || prizeData.name}
            </>
    )
}
export default PrizeContainer