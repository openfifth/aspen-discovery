{strip}
{if !empty($loggedIn)}
	<input type="hidden" id="eventRegistrationUserId-{$eventSourceId}" value="{$userId}">
	{include file="AspenEvents/seats.tpl"}
	{include file="AspenEvents/registrationUserSelector.tpl"}
	{include file="AspenEvents/registrationUserDetails.tpl"}
	{include file="AspenEvents/customRegistrationForm.tpl"}
{else}
	<p>{translate text="Interested in joining our library Events? Use the quick registration form below to sign up and become a library member." isPublicFacing=true}</p>
	<section class="well">
		<div id="selfRegistrationFormContainer">
			{$minimalSelfRegForm}
		</div>
	</section>
	<p>{translate text="Already a member? Log in below to register to this event." isPublicFacing=true}</p></p>
{/if}
{/strip}
