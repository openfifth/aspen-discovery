{strip}
	<div id="main-content">
		{if !empty($loggedIn)}
			{if !empty($profile->_web_note)}
				<div class="row">
					<div id="web_note" class="alert alert-info text-center col-xs-12">{$profile->_web_note}</div>
				</div>
			{/if}
			{if !empty($accountMessages)}
				{include file='systemMessages.tpl' messages=$accountMessages}
			{/if}
			{if !empty($ilsMessages)}
				{include file='ilsMessages.tpl' messages=$ilsMessages}
			{/if}

			<h1>{translate text='Notification Preferences' isPublicFacing=true}</h1>
			{if !empty($profileUpdateErrors)}
				{foreach from=$profileUpdateErrors item=errorMsg}
					<div class="alert alert-danger">{$errorMsg}</div>
				{/foreach}
			{/if}
			{if !empty($profileUpdateMessage)}
				{foreach from=$profileUpdateMessage item=msg}
					<div class="alert alert-success">{$msg}</div>
				{/foreach}
			{/if}
			{if !empty($offline)}
				<div class="alert alert-warning"><strong>{translate text=$offlineMessage isPublicFacing=true}</strong></div>
			{else}
				<div class="grant-notification-permissions">
					<label for="allowNotifications" class="control-label">{translate text='Allow Notifications' isPublicFacing=true}</label>&nbsp;
					{if $edit == true}
						<input type="checkbox" class="form-control" name="allowNotifications" id="allowNotifications" {if $profile->noPromptForUserReviews==1}checked='checked'{/if} data-switch="">
					{else}
						{if $profile->noPromptForUserReviews==0} {translate text='No' isPublicFacing=true}{else} {translate text='Yes' isPublicFacing=true}{/if}
					{/if}
				 </div>
				<div class="notification-permission-controls">
					<label for="notifySavedSearch" class="control-label">{translate text='Allow Notifications for Saved Search' isPublicFacing=true}</label>&nbsp;
					{if $edit == true}
						<input type="checkbox" class="form-control" name="notifySavedSearch" id="notifySavedSearch" {if $profile->noPromptForUserReviews==1}checked='checked'{/if} data-switch="">
					{else}
						{if $profile->noPromptForUserReviews==0} {translate text='No' isPublicFacing=true}{else} {translate text='Yes' isPublicFacing=true}{/if}
					{/if}
					<br><br>
					<label for="notifyCustom" class="control-label">{translate text='Allow Custom Notifications' isPublicFacing=true}</label>&nbsp;
					{if $edit == true}
						<input type="checkbox" class="form-control" name="notifyCustom" id="notifyCustom" {if $profile->noPromptForUserReviews==1}checked='checked'{/if} data-switch="">
					{else}
						{if $profile->noPromptForUserReviews==0} {translate text='No' isPublicFacing=true}{else} {translate text='Yes' isPublicFacing=true}{/if}
					{/if}
					<br><br>
					<label for="notifyAccount" class="control-label">{translate text='Allow Account Notifications' isPublicFacing=true}</label>&nbsp;
					{if $edit == true}
						<input type="checkbox" class="form-control" name="notifyAccount" id="notifyAccount" {if $profile->noPromptForUserReviews==1}checked='checked'{/if} data-switch="">
					{else}
						{if $profile->noPromptForUserReviews==0} {translate text='No' isPublicFacing=true}{else} {translate text='Yes' isPublicFacing=true}{/if}
					{/if}
				</div>
				<script type="module" type="text/javascript">
					import { initialize, appToken } from '/interface/themes/responsive/js/aspen/initFCM.js';
					{* Initiate any checkbox with a data attribute set to data-switch=""  as a bootstrap switch *}
					{literal}
					$(function(){ $('input[type="checkbox"][data-switch]').bootstrapSwitch()});
					$("#usernameHelpButton").on('click', function() {
						var helpButton = $(this);
						if (helpButton.attr("aria-expanded") === "true") {
							$("#usernameHelp").css('display', 'none');
							$("#usernameHelpButton").attr("aria-expanded","false");
						}
						else if (helpButton.attr("aria-expanded") === "false") {
							$("#usernameHelp").css('display', 'block');
							$("#usernameHelpButton").attr("aria-expanded","true");
						}
						return false;
					})
					function handleAllowNotifications() {
						var allow = $("#allowNotifications").is(":checked");
						if(allow)
						{
							let token = initialize();
							console.log(appToken);
							console.log("Token was: "+token);
							if(Notification.permission === "granted")
							{
								$(".grant-notification-permissions").hide();
								$(".notification-permission-controls").show();
							}
						}
					}
					function handleNotificationControls(type) {
						console.log(type + " :: " + $("#"+type).is(":checked"));
						console.log(appToken);
						let value = $("#"+type).is(":checked");
						let postData = {
							"pushToken": appToken,
							"type": type,
							"value": value
						};
						fetch("/AspenPWA/AJAX?method=setNotificationPreference", {
							method: "POST",
							headers: {
								'Cache-Control': 'no-cache'
							},
							body: new URLSearchParams(postData)
						}).then(function (response) {
							console.log(response.json());
						});
					}
					$(document).ready(function(){
						$("#notifySavedSearch").on('switchChange.bootstrapSwitch', function(){handleNotificationControls('notifySavedSearch')});
						$("#notifyAccount").on('switchChange.bootstrapSwitch', function(){handleNotificationControls('notifyAccount')});
						$("#notifyCustom").on('switchChange.bootstrapSwitch', function(){handleNotificationControls('notifyCustom')});
						if(Notification.permission === "granted")
						{
							$(".grant-notification-permissions").hide();
							$(".notification-permission-controls").show();
							initialize();
						} else {
							$(".grant-notification-permissions").show();
							$(".notification-permission-controls").hide();
							$("#allowNotifications").on('switchChange.bootstrapSwitch',handleAllowNotifications);

						}
					});
					{/literal}
				</script>
			{/if}
		{else}
			<div class="page">
				{translate text="You must sign in to view this information." isPublicFacing=true}<a href='/MyAccount/Login' class="btn btn-primary">{translate text="Sign In" isPublicFacing=true}</a>
			</div>
		{/if}
	</div>
{/strip}
