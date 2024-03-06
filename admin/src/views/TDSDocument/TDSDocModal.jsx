import React, { useState } from "react";
import { Button, Input, Modal, ModalBody, ModalHeader, ModalFooter } from 'reactstrap';
import Images from "../../components/images";
import * as NC from "../../helper/NetworkingConstants";
import HF from '../../helper/HelperFunction';
import WSManager from "../../helper/WSManager";
import _ from 'lodash';
import { notify } from 'react-notify-toast';

const TDSDocModal = (props) => {
    const { isOpen, toggle, SelectedFinancialYear, callback } = props;
    const [gov_id, setGovId] = useState('')
    const [tdsDocList, setTDSDocList] = useState([])
    const [uploaded, setUploaded] = useState(true)
    const [posting, setPosting] = useState(false)


    // drag state
    const [dragActive, setDragActive] = React.useState(false);
    // ref
    const inputRef = React.useRef(null);

    // handle drag events
    const handleDrag = (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (e.type === "dragenter" || e.type === "dragover") {
            setDragActive(true);
        } else if (e.type === "dragleave") {
            setDragActive(false);
        }
    };
    const fileValidation = (files) => {
        var allowedExtensions = /(\.pdf)$/i;
        for (const file of files) {
            if (!allowedExtensions.exec(file.name)) {
                notify.show("Invalid media type. allowed media type pdf", "error", 3000)
                return false;
            } else {
                return true
            }
        }
    }
    // triggers when file is dropped
    const handleDrop = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);
        if (fileValidation(e.dataTransfer.files) && e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleFile(e.dataTransfer.files);
        }
    };

    const handleChange = ({ target }) => {
        let value = target.value
        setGovId(value.toUpperCase())
    }

    const handleKeyPress = (e) => {
        const idMaxLimit = HF.getIntVersion() != 1 ? 10 : 50
        let key = e.key;
        if (gov_id.length >= idMaxLimit && e.key !== 'Backspace') {
            e.preventDefault();
        } else {
            console.log("You pressed a key: " + key);
        }
    }

    const isGovIdValid = (govId) => {
        // - ID Format for India accepted 5 alphabets, 4 digits, 1 alphabet (XXXXX0000X) [SAMPLE - AAAAA9999A]
        // - ID Format for Other accepted alphanumeric range 5-50
        const reg_gov_id =  HF.getIntVersion() != 1 ? /^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/ : /^[a-z0-9]{5,50}$/i
        return govId.length > 0 ? reg_gov_id.test(govId) : true
    }
    const doUpload = (file) => {
        let data = new FormData();
        data.append("file_name", file);
        data.append("type", "tds");
        data.append("keep_name", "1");

        WSManager.multipartPost(NC.baseURL + NC.DO_UPLOAD, data).then(({ data, response_code }) => {
            if (response_code == NC.successCode) {
                setTDSDocList([...tdsDocList, data.image_name])
                setUploaded(false)
                setTimeout(() => {
                    setUploaded(true)
                }, 0.5)
            }
        }).catch(error => {
            notify.show("Invalid media type. allowed media type pdf", "error", 3000)
        })
    }
    const onChangeImage = (e) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            if (!file) {
                return;
            }
            doUpload(file)
        }
    }
    const handleFile = (file) => {
        if (file.length == 1) {
            doUpload(file[0])
        } else {
            notify.show("Please upload single file", "error", 3000)
        }
    }
    const removeFile = (image_name) => {
        WSManager.Rest(NC.baseURL + NC.REMOVE_MEDIA, {
            "file_name": image_name, "type": "tds"
        }).then(({ response_code, message }) => {
            if (response_code == NC.successCode) {
                const noB = _.filter(tdsDocList, o => o != image_name)
                setTDSDocList(noB)
                setUploaded(false)
                setTimeout(() => {
                    setUploaded(true)
                }, 0.5)
                // notify.show(message, "success", 3000)
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    const saveTDSDocument = () => {
        setPosting(true)
        let params = {
            "gov_id": gov_id,
            "fy": SelectedFinancialYear.label,
            "document": tdsDocList
        }
        WSManager.Rest(NC.baseURL + NC.SAVE_TDS_DOCUMENT, params).then(({ response_code, message }) => {
            setPosting(false)
            if (response_code == NC.successCode) {
                notify.show(message, "success", 3000)
                toggle()
                callback()
            } else {
                notify.show(message, "error", 3000)
            }
        }).catch(error => {
            setPosting(false)
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    return (
        <>
            <Modal
                size="sm"
                isOpen={isOpen}
                toggle={toggle}
                className="tds-doc-upload"
            >
                <ModalHeader>
                    <span> Document Upload</span>
                    <i className="icon-close" onClick={toggle} />
                </ModalHeader>
                <ModalBody>
                    <div className="form-group">
                        {
                            HF.getIntVersion() != 1 ?
                            <label className="filter-label">PAN Number</label>
                            :
                            <label className="filter-label">ID Number</label>
                        }
                        <Input placeholder=""
                            onChange={handleChange}
                            name="gov_id"
                            value={gov_id}
                            onKeyPress={handleKeyPress}
                            invalid={!isGovIdValid(gov_id)}
                        />
                    </div>
                    <form id="form-file-upload" onDragEnter={handleDrag} onSubmit={(e) => e.preventDefault()}>
                        <Input
                            ref={inputRef}
                            accept=".pdf"
                            type="file"
                            name='TDSDocs'
                            id="TDSDocs"
                            onChange={onChangeImage}
                        />

                        <label htmlFor="TDSDocs" className={dragActive ? "form-upload-box drag-active" : "form-upload-box"}>
                            <span className="form-upload-inner">
                                <img src={Images.IMAGE_GALLARY} alt="" />
                                <h6 className="mt-3">Drag and drop files here</h6>
                                <h6 className="mt-3">OR</h6>
                                <span className="btn btn-outline-primary">Choose Files</span>
                                <ul className="tdsdoc-list" onClick={(e) => e.preventDefault()}>
                                    {
                                        uploaded &&
                                        _.map(tdsDocList, (item, idx) => {
                                            return (
                                                <li key={idx}>
                                                    <span>{item}</span> <i className="icon-close" onClick={() => removeFile(item)} />
                                                </li>
                                            )
                                        })
                                    }
                                </ul>
                            </span>
                        </label>
                        {dragActive && <div id="drag-file-element" onDragEnter={handleDrag} onDragLeave={handleDrag} onDragOver={handleDrag} onDrop={handleDrop}></div>}
                    </form>
                </ModalBody>

                <ModalFooter>
                    <Button color="light" className="ripple" onClick={toggle}>Cancel</Button>
                    <Button
                        color="secondary"
                        className="ripple"
                        disabled={!(gov_id.length > 0 && isGovIdValid(gov_id) && tdsDocList.length > 0 && !posting)}
                        onClick={() => saveTDSDocument()}
                    >Save</Button>
                </ModalFooter>
            </Modal>
        </>
    )

}
export default TDSDocModal