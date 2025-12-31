package com.turning_leaf_technologies.hoopla;

import com.turning_leaf_technologies.logging.BaseIndexingLogEntry;
import org.apache.logging.log4j.Logger;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Date;

public class HooplaExtractLogEntry extends BaseIndexingLogEntry {
	private Long logEntryId = null;
	private int numRegrouped = 0;
	private int numChangedAfterGrouping = 0;
	private int numProducts = 0;
	private int numAdded = 0;
	private int numDeleted = 0;
	private int numUpdated = 0;
	private int numSkipped = 0;
	private int numAvailabilityChanges = 0;

	HooplaExtractLogEntry(Connection dbConn, Logger logger) {
		super(logger);
		try {
			insertLogEntry = dbConn.prepareStatement("INSERT into hoopla_export_log (startTime) VALUES (?)", PreparedStatement.RETURN_GENERATED_KEYS);
			updateLogEntry = dbConn.prepareStatement("UPDATE hoopla_export_log SET lastUpdate = ?, endTime = ?, notes = ?, numProducts = ?, numErrors = ?, numAdded = ?, numUpdated = ?, numDeleted = ?, numSkipped = ?, numRegrouped =?, numChangedAfterGrouping = ?, numInvalidRecords = ?, numAvailabilityChanges = ? WHERE id = ?", PreparedStatement.RETURN_GENERATED_KEYS);
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
				//noinspection DuplicatedCode
				int curCol = 0;
				updateLogEntry.setLong(++curCol, new Date().getTime() / 1000);
				if (endTime == null) {
					updateLogEntry.setNull(++curCol, java.sql.Types.INTEGER);
				} else {
					updateLogEntry.setLong(++curCol, endTime.getTime() / 1000);
				}
				updateLogEntry.setString(++curCol, getNotesHtml());
				updateLogEntry.setInt(++curCol, numProducts);
				updateLogEntry.setInt(++curCol, numErrors);
				updateLogEntry.setInt(++curCol, numAdded);
				updateLogEntry.setInt(++curCol, numUpdated);
				updateLogEntry.setInt(++curCol, numDeleted);
				updateLogEntry.setInt(++curCol, numSkipped);
				updateLogEntry.setInt(++curCol, numRegrouped);
				updateLogEntry.setInt(++curCol, numChangedAfterGrouping);
				updateLogEntry.setInt(++curCol, numInvalidRecords);
				updateLogEntry.setInt(++curCol, numAvailabilityChanges);
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
		this.addNote("Finished Hoopla extraction");
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

	void incNumProducts(int size) {
		numProducts += size;
	}

	void incSkipped() {
		numSkipped++;
	}

	int getNumChanges() {
		return numUpdated + numDeleted + numAdded + numAvailabilityChanges;
	}

	public void incRecordsRegrouped() {
		numRegrouped++;
		if (numRegrouped % 1000 == 0){
			this.saveResults();
		}
	}
	void incAvailabilityChanges(){
		numAvailabilityChanges++;
	}

	public void incChangedAfterGrouping(){
		numChangedAfterGrouping++;
	}

	public int getNumChangedAfterGrouping() {
		return numChangedAfterGrouping;
	}

	public long getLogEntryId() {
		return logEntryId;
	}
}
