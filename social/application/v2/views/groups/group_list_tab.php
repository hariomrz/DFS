<?php $method = $this->router->method ?>
<nav class="secondary-nav">
              <div class="groupleft-action">
                <div class="action-col1">
                  	<div class="text-field-iconright dataTables_filter" id="example_filter">
                    <button class="field-icon icon fa fa-search" id="searchlist"></button>
                      <input type="text" aria-controls="example" class="input-medium" placeholder="Search" id="insearchgrp"/>
                     </div>
                </div>
                <div class="action-col1"> 
                <?php if($method!='publicGroup'){ ?>
                <div class="text-field-select without-chosen">
                      <select name="cardType" id="cardType" data-ng-init="selecttime=''" data-ng-model="selecttime">
                        <option value=""><?php echo lang('sort_by_type') ?></option>
                        <option value="1"><?php echo lang('open') ?></option>
                        <option value="0"><?php echo lang('closed') ?></option>
                      </select>
                  </div>
                  <?php } ?>
                 </div>


              </div>
                <?php //if($this->section=='myzone'){ ?>        
                <div class="groupRight-action">
                  <div>
                    <button type="button" class="btn btn-orange btn-small pull-right block-btn" onclick="changelocation('group/create_group')" ><span class="bold" ><?php echo lang('create').' '.$this->lang->line('new_group') ?></span></button>


                </div>
                <?php //} ?>
              </div>
            </nav>