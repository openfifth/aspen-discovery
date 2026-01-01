package org.aspen_discovery.reindexer;

import com.turning_leaf_technologies.logging.BaseIndexingLogEntry;
import com.turning_leaf_technologies.util.SystemUtils;
import org.apache.logging.log4j.Logger;

import java.sql.*;
import java.util.Date;

public class NightlyIndexLogEntry extends BaseIndexingLogEntry {
	private Long logEntryId = null;
	private int numWorksProcessed;

	private static PreparedStatement insertLogEntry;
	private static PreparedStatement updateLogEntry;

	public NightlyIndexLogEntry(Connection dbConn, Logger logger){
		super(logger);
		try {
			insertLogEntry = dbConn.prepareStatement("INSERT into reindex_log (startTime) VALUES (?)", PreparedStatement.RETURN_GENERATED_KEYS);
			updateLogEntry = dbConn.prepareStatement("UPDATE reindex_log SET lastUpdate = ?, endTime = ?, notes = ?, numWorksProcessed = ?, numErrors = ?, numInvalidRecords = ? WHERE id = ?", PreparedStatement.RETURN_GENERATED_KEYS);
		} catch (SQLException e) {
			logger.error("Error creating prepared statements to update log", e);
		}
		this.saveResults();
	}

	@Override
	public void setFinished() {
		this.endTime = new Date();
		this.addNote("Finished Reindex");
		this.saveResults();
	}

	void incNumWorksProcessed(){
		numWorksProcessed++;
		if (numWorksProcessed % 5000 == 0){
			this.saveResults();
		}
	}

	@Override
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
				int curCol = 0;
				updateLogEntry.setLong(++curCol, new Date().getTime() / 1000);
				if (endTime == null){
					updateLogEntry.setNull(++curCol, Types.INTEGER);
				}else{
					updateLogEntry.setLong(++curCol, endTime.getTime() / 1000);
				}
				updateLogEntry.setString(++curCol, getNotesHtml());
				updateLogEntry.setInt(++curCol, numWorksProcessed);
				updateLogEntry.setInt(++curCol, numErrors);
				updateLogEntry.setInt(++curCol, numInvalidRecords);
				updateLogEntry.setLong(++curCol, logEntryId);
				updateLogEntry.executeUpdate();
			}
			SystemUtils.printMemoryStats(logger);
			return true;
		} catch (SQLException e) {
			logger.error("Error saving nightly indexing log", e);
			return false;
		}
	}
}
