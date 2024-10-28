AspenDiscovery.CommunityEngagement = function() {
    return {
        campaignRewardGiven: function(userId, campaignId) {
            var url = Globals.path + "/Community/AJAX?method=campaignRewardGivenUpdate";
            var params = {
                userId: userId, 
                campaignId: campaignId,
            };
            $.getJSON(url, params, 
                function(data) {
                    if (data.success) {
                        var button = $('.set-reward-btn[data-user-id="' + userId + '"][data-campaign-id="' + campaignId + '"]');
                        button.replaceWith('<span>Reward Given</span>');
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown){
               
                alert('An error occurred while updating the reward status.' + textStatus + ', ' + errorThrown);
                });
        },
        milestoneRewardGiven: function(userId, campaignId, milestoneId) {
            var url = Globals.path + "/Community/AJAX?method=milestoneRewardGivenUpdate";
            var params = {
                userId: userId,
                campaignId: campaignId,
                milestoneId: milestoneId,
            };
            $.getJSON(url, params,
                function(data) {
                    if (data.success) {
                        var button = $('.set-reward-btn-milestone[data-user-id="' + userId + '"][data-campaign-id="' + campaignId + '"][data-milestone-id="' + milestoneId + '"]');
                        button.replaceWith('<span>Milestone Reward Given</span>');
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error: " + textStatus + ", " + errorThrown);
                    console.error("Response Text: " + jqXHR.responseText);
                    alert('An error occurred while updating the reward status for this milestone.' + textStatus + ', ' + errorThrown);
                });
        },
        filterDropdownOptions: function(filterType) {
            var selectedId = ' ';
            if (filterType === 'campaign') {
                selectedId = document.getElementById('campaign_id').value;
            } else if (filterType === 'user') {
                selectedId = document.getElementById('user_id').value;
            }
           
            var url = Globals.path + "/Community/AJAX?method=filterCampaignsAndUsers";
            var params = {
                filterType: filterType,
                id: selectedId
            };

            $.getJSON(url, params, function(data) {
                if (data.success) {
                    console.log(data.items);
                   

                    var campaignDiv = document.createElement('div');

                    if (filterType === 'campaign') {
                        document.getElementById('filteredCampaignList').innerHTML = '';

                        data.items.forEach(function(campaign) {
                            // var campaignDiv = document.createElement('div');
                            campaignDiv.innerText = campaign.name;

                            document.getElementById('filteredCampaignList').appendChild(campaignDiv);
                        }) 

                    } else if (filterType === 'user') {
                        document.getElementById('filteredCampaignsHeader').innerHTML = 'Campaigns for User';

                        data.items.forEach(function(campaign) {
                            campaignDiv.innerHTML = user;
                        })
                        // $('#userDropdown').empty().append('<option value="">All Users</option>');
                        // $.each(data.items, function(index, user) {
                        //     $('#userDropdown').append('<option value=" ' + user.id + '">' + user.username + '</option>');
                        // });
                    }
                } else {
                    alert("Error: " + data.message);
                }
                console.log(data);
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error: " + textStatus +", " + errorThrown);
                alert('An error occurred while fetching filter options.' + textStatus + ', ' + errorThrown);
            });
        }
    }
}(AspenDiscovery.CommunityEngagement || {});