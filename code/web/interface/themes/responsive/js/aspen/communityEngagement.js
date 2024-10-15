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
                        // alert("Reward status updated");
                        var button = $('.set-reward-btn[data-user-id="' + userId + '"][data-campaign-id="' + campaignId + '"]');
                        button.replaceWith('<span>Reward Given</span>');
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