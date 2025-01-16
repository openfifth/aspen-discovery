{strip}
	<header>
		<h1>{translate text="Interlibrary Loan Request" isAdminFacing=true}</h1>
	</header>
	<main>
		<section class="col-xs-10">
			<h2>General information</h2>
			<div class="row">
				<div class="result-label col-tn-4">{translate text='Request Id' isPublicFacing=true}</div>
				<div class="col-tn-8 result-value">
					{$illRequest->oclcRequestId}
				</div>
			</div>
			<div class="row">
				<div class="result-label col-tn-4">{translate text='Status' isPublicFacing=true}</div>
				<div class="col-tn-8 result-value">
					{$illRequest->requestStatus}
				</div>
			</div>
			<div class="row">
				<div class="result-label col-tn-4">{translate text='Status Description' isPublicFacing=true}</div>
				<div class="col-tn-8 result-value">
					{$illRequest->requestStatusDescription}
				</div>
			</div>
			<div class="row">
				<div class="result-label col-tn-4">{translate text='Created On' isPublicFacing=true}</div>
				<div class="col-tn-8 result-value">
					{$illRequest->createdDate|date_format:"%b %d, %Y"}
				</div>
			</div>
			<div class="row">
				<div class="result-label col-tn-4">{translate text='Need By Date' isPublicFacing=true}</div>
				<div class="col-tn-8 result-value">
					{$illRequest->needed|date_format:"%b %d, %Y"}
				</div>
			</div>
			<div class="row">
				<div class="result-label col-tn-4">{translate text='Service Type' isPublicFacing=true}</div>
				<div class="col-tn-8 result-value">
					{$illRequest->serviceType}
				</div>
			</div>
		</section>
		<section class="col-xs-10">
			<h2>Item information</h2>
			<div class="row">
				<div class="result-label col-tn-4">{translate text='Title' isPublicFacing=true}</div>
				<div class="col-tn-8 result-value">
					{$illRequest->title}
				</div>
			</div>
			<div class="row">
				<div class="result-label col-tn-4">{translate text='Author' isPublicFacing=true}</div>
				<div class="col-tn-8 result-value">
					{$illRequest->author}
				</div>
			</div>
			{if ({$illRequest->edition})}
				<div class="row">
					<div class="result-label col-tn-4">{translate text='Edition' isPublicFacing=true}</div>
					<div class="col-tn-8 result-value">
						{$illRequest->edition}
					</div>
				</div>
			{/if}
			{if ({$illRequest->isbn})}
				<div class="row">
					<div class="result-label col-tn-4">{translate text='ISBN' isPublicFacing=true}</div>
					<div class="col-tn-8 result-value">
						{$illRequest->isbn}
					</div>
				</div>
			{/if}
			{if ({$illRequest->issn})}
				<div class="row">
					<div class="result-label col-tn-4">{translate text='ISSN' isPublicFacing=true}</div>
					<div class="col-tn-8 result-value">
						{$illRequest->issn}
					</div>
				</div>
			{/if}
			{if ({$illRequest->oclcNumber})}
				<div class="row">
					<div class="result-label col-tn-4">{translate text='OCLC Number' isPublicFacing=true}</div>
					<div class="col-tn-8 result-value">
						{$illRequest->oclcNumber}
					</div>
				</div>
			{/if}
			<div class="row">
				<div class="result-label col-tn-4">{translate text='Media Type' isPublicFacing=true}</div>
				<div class="col-tn-8 result-value">
					{$illRequest->mediaType}
				</div>
			</div>
			<div class="row">
				<div class="result-label col-tn-4">{translate text='Language' isPublicFacing=true}</div>
				<div class="col-tn-8 result-value">
					{$illRequest->language}
				</div>
			</div>
			{* TODO: if possible, fetch and display permitted actions *}
		</section>
	</main>
{/strip}
