package org.aspendiscovery.palace_project;

import java.sql.ResultSet;
import java.sql.SQLException;

public class PalaceProjectSetting {
	private final String name;
	private final String apiUrl;
	private final String libraryId;
	private final boolean doFullReload;
	private final long lastUpdateOfChangedRecords;
	private final long lastUpdateOfAllRecords;
	private final long id;

	PalaceProjectSetting(ResultSet settingsRS) throws SQLException {
		name = settingsRS.getString("name");
		apiUrl = settingsRS.getString("apiUrl");
		libraryId = settingsRS.getString("libraryId");
		doFullReload = settingsRS.getBoolean("runFullUpdate");
		lastUpdateOfChangedRecords = settingsRS.getLong("lastUpdateOfChangedRecords");
		lastUpdateOfAllRecords = settingsRS.getLong("lastUpdateOfAllRecords");
		id = settingsRS.getLong("id");
	}

	boolean doFullReload() {
		return doFullReload;
	}

	long getId() {
		return id;
	}

	public long getLastUpdateOfChangedRecords() {
		return Math.max(lastUpdateOfChangedRecords, lastUpdateOfAllRecords);
	}

	public String getApiUrl() {
		return apiUrl;
	}

	public String getName() {
		return name;
	}

	public String getLibraryId() {
		return libraryId;
	}
}
