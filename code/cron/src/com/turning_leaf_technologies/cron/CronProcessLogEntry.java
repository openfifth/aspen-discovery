package com.turning_leaf_technologies.cron;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Date;

import com.turning_leaf_technologies.logging.BaseLogEntry;
import org.apache.logging.log4j.Logger;

public class CronProcessLogEntry extends BaseLogEntry {
	private final CronLogEntry cronLogEntry;
	private Long logProcessId;
	private final String processName;
	private int numSkipped;
	private int numUpdates;

	private static PreparedStatement insertLogEntry;
	private static PreparedStatement updateLogEntry;

	public CronProcessLogEntry(CronLogEntry cronLogEntry, String processName, Connection dbConn, Logger logger){
		super(logger);
		this.cronLogEntry = cronLogEntry;
		this.processName = processName;

		try {
			insertLogEntry = dbConn.prepareStatement("INSERT into cron_process_log (cronId, processName, startTime) VALUES (?, ?, ?)", PreparedStatement.RETURN_GENERATED_KEYS);
			updateLogEntry = dbConn.prepareStatement("UPDATE cron_process_log SET lastUpdate = ?, endTime = ?, numErrors = ?, numUpdates = ?, numSkipped = ?, notes = ? WHERE id = ?", PreparedStatement.RETURN_GENERATED_KEYS);
		} catch (SQLException e) {
			logger.error("Error creating prepared statements to update log", e);
		}
	}

	private Date getLastUpdate() {
		//The last time the log entry was updated, so we can tell if a process is stuck
		return new Date();
	}

	public synchronized void incErrors(String note){
		super.incErrors(note);
		cronLogEntry.incErrors();
	}

	public synchronized void incErrors(String note, Exception e){
		super.incErrors(note, e);
		cronLogEntry.incErrors();
	}

	public synchronized void incUpdated() {
		numUpdates++;
		if (numUpdates + numSkipped % 100 == 0) {
			this.saveResults();
		}
	}

	public synchronized void incSkipped() {
		this.numSkipped++;
		if (numUpdates + numSkipped % 100 == 0) {
			this.saveResults();
		}
	}

	public synchronized void addUpdates(int updates) {
		numUpdates += updates;
	}

	public synchronized boolean saveResults() {
		try{
			if (logProcessId == null){
				insertLogEntry.setLong(1, cronLogEntry.getLogEntryId());
				insertLogEntry.setString(2,processName);
				insertLogEntry.setLong(3, startTime.getTime() / 1000);
				insertLogEntry.executeUpdate();
				ResultSet generatedKeys = insertLogEntry.getGeneratedKeys();
				if (generatedKeys.next()){
					logProcessId = generatedKeys.getLong(1);
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
				updateLogEntry.setLong(4, numUpdates);
				updateLogEntry.setLong(5, numSkipped);
				updateLogEntry.setString(6, getNotesHtml());
				updateLogEntry.setLong(7, logProcessId);
				updateLogEntry.executeUpdate();
			}
			return true;
		} catch (SQLException e) {
			logger.error("Error saving cron process log", e);
			return false;
		}
	}
	public void setFinished() {
		this.endTime = new Date();
	}
}
