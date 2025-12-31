package com.turning_leaf_technologies.indexing;

import com.turning_leaf_technologies.logging.BaseIndexingLogEntry;
import org.apache.commons.lang3.StringUtils;
import org.apache.logging.log4j.Logger;

import java.sql.*;
import java.text.SimpleDateFormat;
import java.util.Date;

public class IlsExtractLogEntry extends BaseIndexingLogEntry {
	private Long logEntryId = null;
	private final String indexingProfile;
	private int numRegrouped = 0;
	private int numChangedAfterGrouping = 0;
	private int numProducts = 0;
	private String currentId;
	private boolean isFullUpdate;
	private int numRecordsWithInvalidMarc = 0;
	private int numAdded = 0;
	private int numDeleted = 0;
	private int numUpdated = 0;
	private long numSkipped = 0;
	private int numInvalidRecords = 0;
	private final StringBuilder notesText = new StringBuilder();
	private boolean maxNoteTextLengthReached = false;

	public IlsExtractLogEntry(Connection dbConn, String indexingProfile, Logger logger){
		super(logger);
		this.indexingProfile = indexingProfile;
		try {
			insertLogEntry = dbConn.prepareStatement("INSERT into ils_extract_log (startTime, indexingProfile) VALUES (?, ?)", PreparedStatement.RETURN_GENERATED_KEYS);
			updateLogEntry = dbConn.prepareStatement("UPDATE ils_extract_log SET lastUpdate = ?, isFullUpdate = ?, endTime = ?, notes = ?, numRegrouped =?, numChangedAfterGrouping = ?, numProducts = ?, numRecordsWithInvalidMarc = ?, numErrors = ?, numAdded = ?, numUpdated = ?, numDeleted = ?, numSkipped = ?, currentId = ?, numInvalidRecords = ? WHERE id = ?", PreparedStatement.RETURN_GENERATED_KEYS);
		} catch (SQLException e) {
			logger.error("Error creating prepared statements to update log", e);
		}
		notesText.append("<ol class='cronNotes'>");
		this.saveResults();
	}

	private static PreparedStatement insertLogEntry;
	private static PreparedStatement updateLogEntry;
	@Override
	public synchronized boolean saveResults() {
		try {
			if (logEntryId == null){
				insertLogEntry.setLong(1, startTime.getTime() / 1000);
				insertLogEntry.setString(2, indexingProfile);
				insertLogEntry.executeUpdate();
				ResultSet generatedKeys = insertLogEntry.getGeneratedKeys();
				if (generatedKeys.next()){
					logEntryId = generatedKeys.getLong(1);
				}
			}else{
				int curCol = 0;
				updateLogEntry.setLong(++curCol, new Date().getTime() / 1000);
				updateLogEntry.setBoolean(++curCol, isFullUpdate);
				if (endTime == null){
					updateLogEntry.setNull(++curCol, Types.INTEGER);
				}else{
					updateLogEntry.setLong(++curCol, endTime.getTime() / 1000);
				}
				updateLogEntry.setString(++curCol, getNotesHtml());
				updateLogEntry.setInt(++curCol, numRegrouped);
				updateLogEntry.setInt(++curCol, numChangedAfterGrouping);
				updateLogEntry.setInt(++curCol, numProducts);
				updateLogEntry.setInt(++curCol, numRecordsWithInvalidMarc);
				updateLogEntry.setInt(++curCol, numErrors);
				updateLogEntry.setInt(++curCol, numAdded);
				updateLogEntry.setInt(++curCol, numUpdated);
				updateLogEntry.setInt(++curCol, numDeleted);
				updateLogEntry.setLong(++curCol, numSkipped);
				updateLogEntry.setString(++curCol, currentId);
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

	public void incRecordsWithInvalidMarc(String note) {
		this.numRecordsWithInvalidMarc++;
		this.addNote(note);
		this.saveResults();
	}

	public void incAdded(){
		numAdded++;
	}
	public void incDeleted(){
		numDeleted++;
		if (numDeleted % 1000 == 0){
			this.saveResults();
		}
	}
	public void incUpdated(){
		numUpdated++;
		if (numUpdated % 1000 == 0){
			this.saveResults();
		}
	}
	public void incSkipped(){
		numSkipped++;
	}
	public void incSkipped(long numSkipped){
		this.numSkipped += numSkipped;
	}
	public void setNumProducts(int size) {
		numProducts = size;
	}
	public int getNumProducts(){
		return numProducts;
	}
	public void incProducts(){
		numProducts++;
	}
	public void incRecordsRegrouped() {
		numRegrouped++;
		if (numRegrouped % 1000 == 0){
			this.saveResults();
		}
	}
	public void incChangedAfterGrouping(){
		numChangedAfterGrouping++;
	}

	public int getNumDeleted() {
		return numDeleted;
	}

	public void setNumDeleted(int numDeleted) {
		this.numDeleted = numDeleted;
	}

	public int getNumChangedAfterGrouping() {
		return numChangedAfterGrouping;
	}

	public void setCurrentId(String currentId){
		this.currentId = currentId;
	}

	public void setIsFullUpdate(boolean runFullUpdate) {
		this.isFullUpdate = runFullUpdate;
	}
}
