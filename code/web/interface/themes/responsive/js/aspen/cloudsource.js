AspenDiscovery.CloudSource = (function () {
	return {
		trackCloudSourceUsage: function (id) {
			var ajaxUrl = Globals.path + "/CloudSource/JSON?method=trackCloudSourceUsage&id=" + id;
			$.getJSON(ajaxUrl);
		}
	};
}(AspenDiscovery.CloudSource || {}));