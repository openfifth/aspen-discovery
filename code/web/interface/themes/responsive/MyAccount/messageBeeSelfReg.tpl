{strip}
<h1>{translate text='Register for a Library Card' isPublicFacing=true}</h1>
<div class="page">
	{if !empty($messageBeeSettings)}
		{if !empty($selfRegistrationFormMessage)}
			<div id="selfRegistrationMessage">
				{$selfRegistrationFormMessage}
			</div>
		{/if}

		<div id="messageBeeSelfRegParent">
			<link rel="stylesheet" type="text/css" href="https://messagebee.uniquelibrary.com/external/widgets/patron-registration.css" />
			<script src="https://messagebee.uniquelibrary.com/external/widgets/patron-registration.umd.cjs"></script>

			<script>
				PatronRegistration.register({ldelim}
					customerToken: '{$messageBeeSettings->customerToken}',
					querySelector: '#messageBeeSelfReg'
				{rdelim});
			</script>

			<div id="messageBeeSelfReg">{translate text="Loading..." isPublicFacing=true}</div>
		</div>
	{else}
		{translate text="MessageBee Registration functionality is not properly configured." isPublicFacing=true}
	{/if}
</div>
{/strip}
