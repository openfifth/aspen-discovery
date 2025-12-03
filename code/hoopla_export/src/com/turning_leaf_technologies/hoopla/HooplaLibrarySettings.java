package com.turning_leaf_technologies.hoopla;

import java.sql.ResultSet;
import java.sql.SQLException;

class HooplaLibrarySettings {
	private final long id;
	private final long settingId;
	private final long libraryId;
	private final String displayName;
	private final String hooplaLibraryId;
	private final boolean circulationEnabled;
	private final boolean instantEnabled;
	private final boolean flexEnabled;
	private final boolean fullUpdateForLibrary;
	private final boolean cleanUpInstant;
	private final boolean cleanUpFlex;

	HooplaLibrarySettings(ResultSet settingsRS) throws SQLException {
		id = settingsRS.getLong("id");
		settingId = settingsRS.getLong("settingId");
		libraryId = settingsRS.getLong("libraryId");
		displayName = settingsRS.getString("displayName");
		hooplaLibraryId = settingsRS.getString("hooplaLibraryID");
		circulationEnabled = settingsRS.getBoolean("circulationEnabled");
		instantEnabled = settingsRS.getBoolean("hooplaInstantEnabled");
		flexEnabled = settingsRS.getBoolean("hooplaFlexEnabled");
		fullUpdateForLibrary = settingsRS.getBoolean("fullUpdateForLibrary");
		cleanUpInstant = settingsRS.getBoolean("cleanUpInstant");
		cleanUpFlex = settingsRS.getBoolean("cleanUpFlex");
	}

	long getId() {
		return id;
	}

	long getSettingId() {
		return settingId;
	}

	long getLibraryId() {
		return libraryId;
	}

	String getLibraryDisplayName() {
		return displayName;
	}

	String getHooplaLibraryId() {
		return hooplaLibraryId;
	}

	boolean isInstantEnabled() {
		return instantEnabled;
	}

	boolean isFlexEnabled() {
		return flexEnabled;
	}

	boolean isfullUpdateForLibrary() {
		return fullUpdateForLibrary;
	}

	boolean hasHooplaLibraryId() {
		return hooplaLibraryId != null && !hooplaLibraryId.isEmpty();
	}

	boolean isCirculationEnabled() {
		return circulationEnabled;
	}

	boolean isCleanUpInstant() {
		return cleanUpInstant;
	}

	boolean isCleanUpFlex() {
		return cleanUpFlex;
	}
}
