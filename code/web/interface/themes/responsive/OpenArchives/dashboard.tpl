{strip}
	<div id="main-content" class="col-sm-12">
		<h1>{translate text="Open Archives Dashboard" isAdminFacing=true}</h1>
		{include file="Admin/selectInterfaceForm.tpl"}
		{foreach from=$collections item=collectionName key=collectionId}
			<h2>{$collectionName}</h2>
			<div class="row">
				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h3 class="dashboardCategoryLabel">{translate text="Unique Records Viewed" isAdminFacing=true}
								{' '}
								<a href="/OpenArchives/UsageGraphs?stat=numRecordViewed{if !empty($collectionName)}&subSection={$collectionName}{/if}&instance={$selectedInstance}" title="{translate text="Show Unique Records Viewed Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
							</h3>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisMonth.$collectionId.numRecordViewed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsLastMonth.$collectionId.numRecordViewed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisYear.$collectionId.numRecordViewed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsAllTime.$collectionId.numRecordViewed}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h3 class="dashboardCategoryLabel">{translate text="Total Views" isAdminFacing=true}
								{' '}
								<a href="/OpenArchives/UsageGraphs?stat=numViews{if !empty($collectionName)}&subSection={$collectionName}{/if}&instance={$selectedInstance}" title="{translate text="Show Total Views Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
							</h3>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisMonth.$collectionId.numViews}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsLastMonth.$collectionId.numViews}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisYear.$collectionId.numViews}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsAllTime.$collectionId.numViews}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h3 class="dashboardCategoryLabel">{translate text="Unique Records Used (clicked on)" isAdminFacing=true}
								{' '}
								<a href="/OpenArchives/UsageGraphs?stat=numRecordsUsed{if !empty($collectionName)}&subSection={$collectionName}{/if}&instance={$selectedInstance}" title="{translate text="Show Unique Records Used (clicked on) Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
							</h3>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisMonth.$collectionId.numRecordsUsed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsLastMonth.$collectionId.numRecordsUsed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisYear.$collectionId.numRecordsUsed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsAllTime.$collectionId.numRecordsUsed}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h3 class="dashboardCategoryLabel">{translate text="Total Clicks" isAdminFacing=true}
								{' '}
								<a href="/OpenArchives/UsageGraphs?stat=numClicks{if !empty($collectionName)}&subSection={$collectionName}{/if}&instance={$selectedInstance}" title="{translate text="Show Total Clicks Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
							</h3>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisMonth.$collectionId.numClicks}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsLastMonth.$collectionId.numClicks}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisYear.$collectionId.numClicks}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsAllTime.$collectionId.numClicks}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h3 class="dashboardCategoryLabel">{translate text="Unique Logged In Users" isAdminFacing=true}
								{' '}
								<a href="/OpenArchives/UsageGraphs?stat=activeUsers{if !empty($collectionName)}&subSection={$collectionName}{/if}&instance={$selectedInstance}" title="{translate text="Show Unique Logged In Users Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
							</h3>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisMonth.$collectionId}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersLastMonth.$collectionId}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisYear.$collectionId}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersAllTime.$collectionId}</div>
						</div>
					</div>
				</div>
			</div>
		{/foreach}
	</div>
{/strip}