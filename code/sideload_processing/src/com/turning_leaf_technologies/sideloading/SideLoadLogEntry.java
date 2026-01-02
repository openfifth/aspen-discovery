package com.turning_leaf_technologies.sideloading;

import com.turning_leaf_technologies.logging.BaseIndexingLogEntry;
import org.apache.logging.log4j.Logger;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;

class SideLoadLogEntry extends BaseIndexingLogEntry {
	private Long logEntryId = null;
	private int numSideLoadsUpdated = 0;
	private String sideLoadsUpdated = "";
	private int numProducts = 0;
	private int numAdded = 0;
	private int numDeleted = 0;
	private int numUpdated = 0;
	private int numSkipped = 0;

	SideLoadLogEntry(Connection dbConn, Logger logger) {
		super(logger);
		try {
			insertLogEntry = dbConn.prepareStatement("INSERT into sideload_log (startTime) VALUES (?)", PreparedStatement.RETURN_GENERATED_KEYS);
			updateLogEntry = dbConn.prepareStatement("UPDATE sideload_log SET lastUpdate = ?, endTime = ?, notes = ?, numSideLoadsUpdated = ?, sideLoadsUpdated = ?, numProducts = ?, numErrors = ?, numAdded = ?, numUpdated = ?, numDeleted = ?, numSkipped = ?, numInvalidRecords = ? WHERE id = ?", PreparedStatement.RETURN_GENERATED_KEYS);
		} catch (SQLException e) {
			logger.error("Error creating prepared statements to update log", e);
		}
		saveResults();
	}

	private static PreparedStatement insertLogEntry;
	private static PreparedStatement updateLogEntry;

	public boolean saveResults() {
		try {
			if (logEntryId == null) {
				insertLogEntry.setLong(1, startTime.getTime() / 1000);
				insertLogEntry.executeUpdate();
				ResultSet generatedKeys = insertLogEntry.getGeneratedKeys();
				if (generatedKeys.next()) {
					logEntryId = generatedKeys.getLong(1);
				}
			} else {
				int curCol = 0;
				updateLogEntry.setLong(++curCol, new Date().getTime() / 1000);
				if (endTime == null) {
					updateLogEntry.setNull(++curCol, java.sql.Types.INTEGER);
				} else {
					updateLogEntry.setLong(++curCol, endTime.getTime() / 1000);
				}
				updateLogEntry.setString(++curCol, getNotesHtml());
				updateLogEntry.setInt(++curCol, numSideLoadsUpdated);
				updateLogEntry.setString(++curCol, sideLoadsUpdated);
				updateLogEntry.setInt(++curCol, numProducts);
				updateLogEntry.setInt(++curCol, numErrors);
				updateLogEntry.setInt(++curCol, numAdded);
				updateLogEntry.setInt(++curCol, numUpdated);
				updateLogEntry.setInt(++curCol, numDeleted);
				updateLogEntry.setInt(++curCol, numSkipped);
				updateLogEntry.setInt(++curCol, numInvalidRecords);
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
		this.addNote("Finished Processing Side Loads");
		this.saveResults();
	}

	void incAdded() {
		numAdded++;
	}

	void incDeleted() {
		numDeleted++;
	}

	void incUpdated() {
		numUpdated++;
	}

	@SuppressWarnings("SameParameterValue")
	void incNumProducts(int size) {
		numProducts += size;
	}

	void addUpdatedSideLoad(String sideLoadName) {
		numSideLoadsUpdated++;
		if (!sideLoadsUpdated.isEmpty()) {
			sideLoadsUpdated += ",";
		}
		sideLoadsUpdated += sideLoadName;
		this.saveResults();
	}

	void incSkipped() {
		numSkipped++;
	}

	int getNumProducts() {
		return numProducts;
	}

	public int getNumDeleted() {
		return numDeleted;
	}
}
