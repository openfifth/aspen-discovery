package com.turning_leaf_technologies.reindexer;

import com.turning_leaf_technologies.logging.BaseLogEntry;
import org.apache.logging.log4j.Logger;

import java.sql.*;
import java.util.Date;

class ListIndexingLogEntry extends BaseLogEntry {
	private Long logEntryId = null;
	private int numLists = 0;
	private int numAdded = 0;
	private int numDeleted = 0;
	private int numUpdated = 0;
	private int numSkipped = 0;

    ListIndexingLogEntry(Connection dbConn, Logger logger){
		super(logger);
		try {
			insertLogEntry = dbConn.prepareStatement("INSERT into list_indexing_log (startTime) VALUES (?)", PreparedStatement.RETURN_GENERATED_KEYS);
			updateLogEntry = dbConn.prepareStatement("UPDATE list_indexing_log SET lastUpdate = ?, endTime = ?, notes = ?, numLists = ?, numErrors = ?, numAdded = ?, numUpdated = ?, numDeleted = ?, numSkipped = ? WHERE id = ?", PreparedStatement.RETURN_GENERATED_KEYS);
		} catch (SQLException e) {
			logger.error("Error creating prepared statements to update log", e);
		}
		saveResults();
	}

	private static PreparedStatement insertLogEntry;
	private static PreparedStatement updateLogEntry;
	public boolean saveResults() {
		try {
			if (logEntryId == null){
				insertLogEntry.setLong(1, startTime.getTime() / 1000);
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
				updateLogEntry.setInt(++curCol, numLists);
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
	public void setFinished() {
		this.endTime = new Date();
		this.addNote("Finished List indexing");
		this.saveResults();
	}
	void incAdded(){
		numAdded++;
	}
	void incDeleted(){
		numDeleted++;
	}
	void incUpdated(){
		numUpdated++;
	}
	void incSkipped(){
		numSkipped++;
	}
	void setNumLists(int size) {
		numLists = size;
	}
}
