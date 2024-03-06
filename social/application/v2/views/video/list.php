<div ng-controller="WallPostCtrl">
	<ul>
		<li ng-repeat="pl in playlist">
			<a ng-href="{{'<?php site_url('video') ?>/play/'+pl.PlaylistID}}" ng-bind="pl.PlaylistName"></a>
		</li>
	</ul>
</div>