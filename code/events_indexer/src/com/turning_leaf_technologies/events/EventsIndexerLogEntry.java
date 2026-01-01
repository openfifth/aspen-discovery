package com.turning_leaf_technologies.events;

import com.turning_leaf_technologies.logging.BaseLogEntry;
import org.apache.logging.log4j.Logger;

import java.sql.*;
import java.util.Date;

public class EventsIndexerLogEntry extends BaseLogEntry {
	private Long id;
	private int numEvents = 0;
	private int numAdded = 0;
	private int numDeleted = 0;
	private int numUpdated = 0;

	private static PreparedStatement insertLogEntry;
	private static PreparedStatement updateLogEntry;

	private final String name;

	EventsIndexerLogEntry(String name, Connection dbConn, Logger logger) {
		super(logger);
		this.name = name;

		try {
			insertLogEntry = dbConn.prepareStatement("INSERT into events_indexing_log (name, startTime) VALUES (?, ?)", PreparedStatement.RETURN_GENERATED_KEYS);
			updateLogEntry = dbConn.prepareStatement("UPDATE events_indexing_log SET lastUpdate = ?, endTime = ?, notes = ?, numEvents = ?, numErrors = ?, numAdded = ?, numUpdated = ?, numDeleted = ? WHERE id = ?", PreparedStatement.RETURN_GENERATED_KEYS);
		} catch (SQLException e) {
			logger.error("Error creating prepared statements to update log", e);
		}
		saveResults();
	}

	public boolean saveResults() {
		try {
			if (id == null) {
				insertLogEntry.setString(1, name);
				insertLogEntry.setLong(2, startTime.getTime() / 1000);
				insertLogEntry.executeUpdate();
				ResultSet generatedKeys = insertLogEntry.getGeneratedKeys();
				if (generatedKeys.next()) {
					id = generatedKeys.getLong(1);
				}
			} else {
				int curCol = 0;
				updateLogEntry.setLong(++curCol, new Date().getTime() / 1000);
				if (endTime == null) {
					updateLogEntry.setNull(++curCol, Types.INTEGER);
				} else {
					updateLogEntry.setLong(++curCol, endTime.getTime() / 1000);
				}
				updateLogEntry.setString(++curCol, getNotesHtml());
				updateLogEntry.setInt(++curCol, numEvents);
				updateLogEntry.setInt(++curCol, numErrors);
				updateLogEntry.setInt(++curCol, numAdded);
				updateLogEntry.setInt(++curCol, numUpdated);
				updateLogEntry.setInt(++curCol, numDeleted);
				updateLogEntry.setLong(++curCol, id);
				updateLogEntry.executeUpdate();
			}
			return true;
		} catch (SQLException e) {
			logger.error("Error creating updating log", e);
			return false;
		}
	}

	void incAdded() {
		numAdded++;
		if (numAdded % 50 == 0){
			this.saveResults();
		}
	}

	void incDeleted() {
		numDeleted++;
	}

	void incDeletedByNum(int num) {
		numDeleted += num;
	}

	void incUpdated() {
		numUpdated++;
		if (numUpdated % 50 == 0){
			this.saveResults();
		}
	}

	void incNumEvents(int numResults) {
		this.numEvents += numResults;
	}

}
