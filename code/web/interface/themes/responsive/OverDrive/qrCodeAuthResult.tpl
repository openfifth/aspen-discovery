{strip}
	<div class="container">
		<div class="row">
				<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
					<h1 class="text-center">{if !empty($qrResultTitle)}{$qrResultTitle}{else}{$readerName}{/if}</h1>
				<div class="alert {if $qrResultSuccess}alert-success{else}alert-danger{/if}">
					{$qrResultMessage}
				</div>
				<p class="text-center">
					<a href="/MyAccount/OverDriveOptions" class="btn btn-primary">
						{translate text="Return to %1% Options" 1=$readerName isPublicFacing=true}
					</a>
				</p>
			</div>
		</div>
	</div>
{/strip}
