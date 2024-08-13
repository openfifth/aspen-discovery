{strip}
    <div id="main-content" class="col-sm-12">
        <h1>{translate text="Dashboard" isAdminFacing=true}</h1>

        {*All Campaigns*}
        <div class="dashboardCategory row" style="border: 1px solid #3174AF;padding:0 10px 10px 10px; margin-bottom: 10px;">
            <div class="col-sm-12">
                <h2 class="dashboardCategoryLabel">{translate text="All Campaigns" isAdminFacing=true}</h2>
                {foreach from=$campaigns item=campaign}
                    <div style="border-bottom: 2px solid #3174AF;padding: 10px; margin-bottom; 10px;">
                       <h5 style="font-weight:bold;">{translate text=$campaign->name isAdminFacing=true}</h5>

                        <div class="dashboardLabel">Number of Patrons Enrolled:</div>
                        <div class="dashboardValue">{translate text=$campaign->currentEnrollments isAdminFacing=true}</div>

                        <div class="dashboardLabel">Total Number of Enrollments:</div>
                        <div class="dashboardValue">{translate text=$campaign->enrollmentCounter isAdminFacing=true}</div>

                        <div class="dashboardLabel">Total Number of Unenrollments:</div>
                        <div class="dashboardValue">{translate text=$campaign->unenrollmentCounter isAdminFacing=true}</div>
                    </div>
                {/foreach}
            </div>
        </div>

    {*Active Campaigns*}
      <div class="dashboardCategory row" style="border: 1px solid #3174AF;padding: 0 10px 10px 10px; margin-bottom: 10px;">
      <div class="col-sm-12">
          <h2 class="dashboardCategoryLabel">{translate text="Active Campaigns" isAdminFacing=true}</h2>
          {foreach from=$activeCampaigns item=activeCampaign}
              <div style="border-bottom: 2px solid #3174AF;padding: 10px; margin-bottom; 10px;">
                  <h5 style="font-weight:bold;">{translate text=$activeCampaign->name isAdminFacing=true}</h5>

                  <div class="dashboardLabel">Number of Patrons Enrolled:</div>
                  <div class="dashboardValue">{translate text=$activeCampaign->currentEnrollments isAdminFacing=true}</div>

                  <div class="dashboardLabel">Total Number of Enrollments:</div>
                  <div class="dashboardValue">{translate text=$activeCampaign->enrollmentCounter isAdminFacing=true}</div>

                  <div class="dashboardLabel">Total Number of Unenrollments:</div>
                  <div class="dashboardValue">{translate text=$activeCampaign->unenrollmentCounter isAdminFacing=true}</div>
              </div>
          {/foreach}
      </div>
  </div>

     {*Upcoming Campaigns*}
     <div class="dashboardCategory row" style="border: 1px solid #3174AF;padding: 0 10px 10px 10px; margin-bottom: 10px;">
     <div class="col-sm-12">
         <h2 class="dashboardCategoryLabel">{translate text="Upcoming Campaigns" isAdminFacing=true}</h2>
         {foreach from=$upcomingCampaigns item=upcomingCampaign}
             <div style="border-bottom: 2px solid #3174AF;padding: 10px; margin-bottom; 10px;">
                 <h5 style="font-weight:bold;">{translate text=$campaign->name isAdminFacing=true}</h5>

                 <div class="dashboardLabel">Number of Patrons Enrolled:</div>
                 <div class="dashboardValue">{translate text=$upcomingCampaign->currentEnrollments isAdminFacing=true}</div>

                 <div class="dashboardLabel">Total Number of Enrollments:</div>
                 <div class="dashboardValue">{translate text=$upcomingCampaign->enrollmentCounter isAdminFacing=true}</div>

                 <div class="dashboardLabel">Total Number of Unenrollments:</div>
                 <div class="dashboardValue">{translate text=$upcomimgCampaign->unenrollmentCounter isAdminFacing=true}</div>
             </div>
         {/foreach}
     </div>
 </div>

        {*Campaigns that started this month*}
        <div class="dashboardCategory row" style="border: 1px solid #3174AF;padding: 0 10px 10px 10px; margin-bottom: 10px;">
        <div class="col-sm-12">
            <h2 class="dashboardCategoryLabel">{translate text="Campaigns Starting This Month" isAdminFacing=true}</h2>
            {foreach from=$campaignsThisMonth item=campaignMonth}
                <div style="border-bottom: 2px solid #3174AF;padding: 10px; margin-bottom; 10px;">
                  <h5 style="font-weight:bold;">{translate text=$campaignMonth->name isAdminFacing=true}</h5>

                    <div class="dashboardLabel">Number of Patrons Enrolled:</div>
                    <div class="dashboardValue">{translate text=$campaignMonth->currentEnrollments isAdminFacing=true}</div>

                    <div class="dashboardLabel">Total Number of Enrollments:</div>
                    <div class="dashboardValue">{translate text=$campaignMonth->enrollmentCounter isAdminFacing=true}</div>

                    <div class="dashboardLabel">Total Number of Unenrollments:</div>
                    <div class="dashboardValue">{translate text=$campaignMonth->unenrollmentCounter isAdminFacing=true}</div>
                </div>
            {/foreach}
        </div>
    </div>

      {*Campaigns that end this month*}
      <div class="dashboardCategory row" style="border: 1px solid #3174AF;padding: 0 10px 10px 10px; margin-bottom: 10px;">
      <div class="col-sm-12">
          <h2 class="dashboardCategoryLabel">{translate text="Campaigns Ending This Month" isAdminFacing=true}</h2>
          {foreach from=$campaignsEndingThisMonth item=campaignEnding}
              <div style="border-bottom: 2px solid #3174AF;padding: 10px; margin-bottom; 10px;">
                 <h5 style="font-weight:bold;">{translate text=$campaignEnding->name isAdminFacing=true}</h5>

                  <div class="dashboardLabel">Number of Patrons Enrolled:</div>
                  <div class="dashboardValue">{translate text=$campaignEnding->currentEnrollments isAdminFacing=true}</div>

                  <div class="dashboardLabel">Total Number of Enrollments:</div>
                  <div class="dashboardValue">{translate text=$campaignEnding->enrollmentCounter isAdminFacing=true}</div>

                  <div class="dashboardLabel">Total Number of Unenrollments:</div>
                  <div class="dashboardValue">{translate text=$campaignEnding->unenrollmentCounter isAdminFacing=true}</div>
              </div>
          {/foreach}
      </div>
  </div>

     

     

   


    </div>
{/strip}