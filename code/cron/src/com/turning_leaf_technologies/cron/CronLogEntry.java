package com.turning_leaf_technologies.cron;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.Date;

import com.turning_leaf_technologies.logging.BaseLogEntry;
import org.apache.logging.log4j.Logger;

public class CronLogEntry extends BaseLogEntry {
	private Long logEntryId = null;

	private static PreparedStatement insertLogEntry;
	private static PreparedStatement updateLogEntry;

	public CronLogEntry(Connection dbConn, Logger logger){
		super(logger);

		try {
			insertLogEntry = dbConn.prepareStatement("INSERT into cron_log (startTime) VALUES (?)", PreparedStatement.RETURN_GENERATED_KEYS);
			updateLogEntry = dbConn.prepareStatement("UPDATE cron_log SET lastUpdate = ?, endTime = ?, numErrors = ?, notes = ? WHERE id = ?", PreparedStatement.RETURN_GENERATED_KEYS);
		} catch (SQLException e) {
			logger.error("Error creating prepared statements to update log", e);
		}
	}
	private Date getLastUpdate() {
		//The last time the log entry was updated, so we can tell if a process is stuck
		return new Date();
	}
	Long getLogEntryId() {
		return logEntryId;
	}

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
				//noinspection DuplicatedCode
				updateLogEntry.setLong(1, getLastUpdate().getTime() / 1000);
				if (endTime == null){
					updateLogEntry.setNull(2, java.sql.Types.INTEGER);
				}else{
					updateLogEntry.setLong(2, endTime.getTime() / 1000);
				}
				updateLogEntry.setLong(3, numErrors);
				updateLogEntry.setString(4, getNotesHtml());
				updateLogEntry.setLong(5, logEntryId);
				updateLogEntry.executeUpdate();
			}
			return true;
		} catch (SQLException e) {
			logger.error("Error creating prepared statements to update log", e);
			return false;
		}
	}
	public void setFinished() {
		this.endTime = new Date();
	}

	void incErrors(){
		numErrors++;
		this.saveResults();
	}
}
