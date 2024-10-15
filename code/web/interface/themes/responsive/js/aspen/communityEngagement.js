AspenDiscovery.CommunityEngagement = function() {
    return {
        campaignRewardGiven: function(userId, campaignId) {
            console.log("Campaign Reward");
            var url = Globals.path + "/Community/AJAX?method=campaignRewardGivenUpdate";
            var params = {
                // method: 'campaignRewardGivenUpdate',
                userId: userId, 
                campaignId: campaignId,
            };
            $.getJSON(url, params, 
                function(data) {
                    if (data.success) {
                        alert("Reward status updated");
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown){
               
                alert('An error occurred while updating the reward status.' + textStatus + ', ' + errorThrown);
                });
        }
    }
}(AspenDiscovery.CommunityEngagement || {});