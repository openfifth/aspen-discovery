package com.turning_leaf_technologies.indexing;

public class OverDriveScope {
	private long id;
	private long settingId;
	private String settingName;
	private String scopeName;
	private boolean includeAdult;
	private boolean includeTeen;
	private boolean includeKids;
	private boolean suppressKindleFormat;
	//Reader Name from the OverDrive Setting
	private String readerName;

	public long getId() {
		return id;
	}

	public void setId(long id) {
		this.id = id;
	}

	public String getScopeName() {
		return scopeName;
	}

	public void setScopeName(String scopeName) {
		this.scopeName = scopeName;
	}

	public boolean isIncludeAdult() {
		return includeAdult;
	}

	public String getReaderName() {
		return readerName;
	}

	public void setReaderName(String readerName) {
		this.readerName = readerName;
	}

	void setIncludeAdult(boolean includeAdult) {
		this.includeAdult = includeAdult;
	}

	public boolean isIncludeTeen() {
		return includeTeen;
	}

	void setIncludeTeen(boolean includeTeen) {
		this.includeTeen = includeTeen;
	}

	public boolean isIncludeKids() {
		return includeKids;
	}

	void setIncludeKids(boolean includeKids) {
		this.includeKids = includeKids;
	}

	public boolean isSuppressKindleFormat() {
		return suppressKindleFormat;
	}

	void setSuppressKindleFormat(boolean suppressKindleFormat) {
		this.suppressKindleFormat = suppressKindleFormat;
	}

	public long getSettingId() {
		return settingId;
	}

	void setSettingId(long settingId) {
		this.settingId = settingId;
	}

	public String getSettingName() {
		return settingName;
	}

	public void setSettingName(String settingName) {
		this.settingName = settingName;
	}
}
