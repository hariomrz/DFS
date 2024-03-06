<div class="tab-pane fade" id="Notes">
    
            <div class="section-content clearfix">
                <button class="btn btn-default pull-right" ng-click="reset_popup();" data-toggle="modal" data-target="#addNotes">ADD NOTES</button>
            </div>
            <!-- Post Start -->
            
            <!-- Post Ends -->
            <ul class="note-list clearfix">
                <li ng-repeat="Notes in NotesList">
                    <div class="list-header">
                        <span ng-bind="Notes.CreatedDate"></span>
                        <div class="action-group">
                            <i class="ficon-edit" ng-click="open_edit_popup(Notes,$index);"></i>
                            <i class="ficon-bin" ng-click="deleteNote(Notes.NoteID,$index);"></i>
                        </div>
                    </div>
                    <p ng-bind-html="Notes.Description"></p>
                </li>
            </ul>
</div>