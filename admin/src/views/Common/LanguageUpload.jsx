import React, { Component } from 'react';
import { Col, Row,Button,Input } from 'reactstrap';
import _ from "lodash";
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";

// React select
import Select from 'react-select';
import 'react-select/dist/react-select.min.css';
import { notify } from 'react-notify-toast';
import HF from '../../helper/HelperFunction';
var globalThis = null;
export default class LanguageUpload extends Component {

  constructor(props) {
    super(props);

    this.state = {
      language_list: HF.getLanguageData() ? HF.getLanguageData() : [],
      current_lang :'',
      current_lang_label :'',
      updatedCSV:'',
      updatedMaster:''
    };
  }

  componentDidMount(){
    globalThis = this;
  }
  
  selectedlanguage = (selectedOption) => {
    if (!selectedOption) {
      return false;
    }
    this.setState({
      current_lang_label:selectedOption.label,
      current_lang:selectedOption.value
    },   
    );   
  }

  exportLang = (lang)=> {		
		if(lang =='')
		{
			return false;
		}

		var key = WSManager.getToken();
		var url = "common/export_language/"+lang+"/?"+NC.ADMIN_AUTH_KEY+"="+key;
		window.open(NC.baseURL+NC.ADMIN_FOLDER_NAME+url);
  };
  
  uploadCSV() {
    this.setState({ isUploadingFlag: true });
    var data = new FormData();
    data.append("file", this.state.updatedCSV);    
    var xhr = new XMLHttpRequest();
    xhr.withCredentials = false;
    xhr.addEventListener("readystatechange", function () {
        if (this.readyState === 4) {          
            var response = JSON.parse(this.responseText);            
            globalThis.setState({ isUploadingFlag: false });
            if (response != '' && response.response_code === NC.successCode) {
               notify.show("File uploaded", "success", 5000);
            }
            else {
              notify.show(response.message, "error", 5000);
            }
        }
    });
    
    var auth_key = WSManager.getToken();
    xhr.open("POST", NC.baseURL +NC.DO_UPLOAD_LANG+this.state.current_lang+'/'+NC.ADMIN_AUTH_KEY+'='+auth_key);
    xhr.send(data);
  }

  uploadMaster() {
    this.setState({ isUploadingFlag: true });
    var data = new FormData();
    data.append("file", this.state.updatedMaster);    
    var xhr = new XMLHttpRequest();
    xhr.withCredentials = false;
    xhr.addEventListener("readystatechange", function () {
        if (this.readyState === 4) {          
            var response = JSON.parse(this.responseText);            
            globalThis.setState({ isUploadingFlag: false });
            if (response != '' && response.response_code === NC.successCode) {
               notify.show("File uploaded", "success", 5000);
            }
            else {
              notify.show(response.message, "error", 5000);              
            }
        }
    });
    
    var auth_key = WSManager.getToken();
    xhr.open("POST", NC.baseURL +NC.DO_UPLOAD_MASTER_FILE+this.state.current_lang+'/'+NC.ADMIN_AUTH_KEY+'='+auth_key);
    xhr.send(data);
  }


  onDropCSV(e) {
    
    e.preventDefault();
    let reader = new FileReader();
    let mImage = e.target.files[0];
    if(e.target.name == 'importLanguage')
    {
      reader.onloadend = () => {
        this.setState({
          updatedCSV: mImage,          
        },function(){
            this.uploadCSV();
        });
      }
    } 
    reader.readAsDataURL(mImage)    
  }

  onDropMaster(e) {    
    e.preventDefault();
    let reader = new FileReader();
    let mImage = e.target.files[0];
    if(e.target.name == 'importMasterLanguage')
    {
      reader.onloadend = () => {
        this.setState({
          updatedMaster: mImage,
        },function(){
            this.uploadMaster();
        });
      }
    }    
  }

  render() {
    return (
      <div className="animated fadeIn">
        <div>
        <Row>
              <Col xl="6" sm="8">
                <Select
                clearable={false}
                  className="sports-seletor"
                  name="selected_language"
                  placeholder="Select Language"
                  menuIsOpen={true}
                  value={this.state.current_lang}
                  options={this.state.language_list}
                  onChange={(value) => this.selectedlanguage(value)}
                />
              </Col>          
        </Row>
        <hr></hr>
        <Row>
              <Col xl="6" sm="8">
                <Button className="btn-secondary" onClick={() => this.exportLang(this.state.current_lang)}>Export {this.state.current_lang_label}</Button>
                                
              </Col>
        </Row>
        <hr></hr>
        <Row>
              <Col xl="6" sm="8">
                <Input className="d-none" id="importLanguage" type="file" name="importLanguage" placeholder="import language"
                          accept="csv/*"
                          ref={(ref) => this.upload = ref}
                          onChange={this.onDropCSV.bind(this)}
                      />
                      <label className="btn-secondary" htmlFor="importLanguage">Import  {this.state.current_lang_label}</label>
                                
              </Col>
        </Row>
        <hr></hr>
        <Row>
              <Col xl="6" sm="8">
              <Input className="d-none" id="importMasterLanguage" type="file" name="importMasterLanguage" placeholder="import master language"                          ref={(ref) => this.upload = ref}
                          onChange={this.onDropMaster.bind(this)}
                      />
              <label className="btn-secondary" htmlFor="importMasterLanguage">Upload   {this.state.current_lang_label} Master FIle to Bucket</label>
              </Col>
        </Row>
        </div>
       
       </div>
    );
  }
}
