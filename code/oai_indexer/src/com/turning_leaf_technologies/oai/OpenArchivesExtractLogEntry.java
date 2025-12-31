package com.turning_leaf_technologies.oai;

import com.turning_leaf_technologies.logging.BaseLogEntry;
import org.apache.logging.log4j.Logger;

import java.sql.*;
import java.util.Date;

class OpenArchivesExtractLogEntry extends BaseLogEntry {
	private Long logEntryId = null;
	private final String collectionName;
	private int numRecords = 0;
	private int numAdded = 0;
	private int numDeleted = 0;
	private int numUpdated = 0;
	private int numSkipped = 0;
	private int saveCounter = 0;
	private static final int SAVE_FREQUENCY = 500;

    OpenArchivesExtractLogEntry(String collectionName, Connection dbConn, Logger logger){
		super(logger);
		this.collectionName = collectionName;
		try {
			insertLogEntry = dbConn.prepareStatement("INSERT into open_archives_export_log (collectionName, startTime) VALUES (?, ?)", PreparedStatement.RETURN_GENERATED_KEYS);
			updateLogEntry = dbConn.prepareStatement("UPDATE open_archives_export_log SET lastUpdate = ?, endTime = ?, notes = ?, numRecords = ?, numErrors = ?, numAdded = ?, numUpdated = ?, numDeleted = ?, numSkipped = ? WHERE id = ?", PreparedStatement.RETURN_GENERATED_KEYS);
		} catch (SQLException e) {
			logger.error("Error creating prepared statements to update log: ", e);
		}
		saveResults();
	}

	private static PreparedStatement insertLogEntry;
	private static PreparedStatement updateLogEntry;
	public boolean saveResults() {
		try {
			if (logEntryId == null){
				insertLogEntry.setString(1, collectionName);
				insertLogEntry.setLong(2, startTime.getTime() / 1000);
				insertLogEntry.executeUpdate();
				ResultSet generatedKeys = insertLogEntry.getGeneratedKeys();
				if (generatedKeys.next()){
					logEntryId = generatedKeys.getLong(1);
				}
			}else{
				int curCol = 0;
				updateLogEntry.setLong(++curCol, new Date().getTime() / 1000);
				if (endTime == null){
					updateLogEntry.setNull(++curCol, Types.INTEGER);
				}else{
					updateLogEntry.setLong(++curCol, endTime.getTime() / 1000);
				}
				updateLogEntry.setString(++curCol, getNotesHtml());
				updateLogEntry.setInt(++curCol, numRecords);
				updateLogEntry.setInt(++curCol, numErrors);
				updateLogEntry.setInt(++curCol, numAdded);
				updateLogEntry.setInt(++curCol, numUpdated);
				updateLogEntry.setInt(++curCol, numDeleted);
				updateLogEntry.setInt(++curCol, numSkipped);
				updateLogEntry.setLong(++curCol, logEntryId);
				updateLogEntry.executeUpdate();
			}
			return true;
		} catch (SQLException e) {
			logger.error("Error creating updating log", e);
			return false;
		}
	}

	private void saveResultsPeriodically() {
		saveCounter++;
		if (saveCounter >= SAVE_FREQUENCY) {
			saveResults();
			saveCounter = 0;
		}
	}
	void incAdded(){
		numAdded++;
		saveResultsPeriodically();
	}
	void incDeleted(){
		numDeleted++;
		saveResultsPeriodically();
	}
	void incSkipped(){
		numSkipped++;
		saveResultsPeriodically();
	}
	void incUpdated(){
		numUpdated++;
		saveResultsPeriodically();
	}
	@SuppressWarnings("unused")
	void setNumRecords(int size) {
		numRecords = size;
	}

	void incNumRecords() {
		this.numRecords++;
	}

}
