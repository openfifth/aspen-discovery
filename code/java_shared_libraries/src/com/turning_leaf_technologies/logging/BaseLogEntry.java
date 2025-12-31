package com.turning_leaf_technologies.logging;

import org.apache.commons.lang3.StringUtils;
import org.apache.logging.log4j.Logger;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Date;
import java.util.List;

public abstract class BaseLogEntry {
	protected final Date startTime;
	protected Date endTime;
	private final List<String> notes = Collections.synchronizedList(new ArrayList<>());
	protected int numErrors = 0;

	protected final Logger logger;

	public BaseLogEntry(Logger logger){
		this.logger = logger;
		this.startTime = new Date();
	}

	final SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
	//Synchronized to prevent concurrent modification of the notes ArrayList
	public synchronized void addNote(String note) {
		Date date = new Date();
		this.notes.add(dateFormat.format(date) + " - " + note);
		logger.info(note);
	}

	public abstract boolean saveResults();

	public void setFinished() {
		this.endTime = new Date();
		this.addNote("Finished Events Indexing");
		this.saveResults();
	}

	public void incErrors(String note) {
		this.addNote("ERROR: " + note);
		numErrors++;
		this.saveResults();
		logger.error(note);
	}

	public void incErrors(String note, Exception e){
		this.addNote("ERROR: " + note + " " + e.toString());
		numErrors++;
		this.saveResults();
		logger.error(note, e);
	}

	public void incErrors(String note, Error e){
		this.addNote("ERROR: " + note + " " + e.toString());
		numErrors++;
		this.saveResults();
		logger.error(note, e);
	}

	@SuppressWarnings("unused")
	public boolean hasErrors() {
		return numErrors > 0;
	}

	public String getNotesHtml() {
		StringBuilder notesText = new StringBuilder("<ol class='cronNotes'>");
		for (String curNote : notes) {
			String cleanedNote = curNote;
			cleanedNote = StringUtils.replace(cleanedNote, "<pre>", "<code>");
			cleanedNote = StringUtils.replace(cleanedNote,"</pre>", "</code>");
			//Replace multiple line breaks
			cleanedNote = cleanedNote.replaceAll("(?:<br?>\\s*)+", "<br/>");
			cleanedNote = cleanedNote.replaceAll("<meta.*?>", "");
			cleanedNote = cleanedNote.replaceAll("<title>.*?</title>", "");
			notesText.append("<li>").append(cleanedNote).append("</li>");
		}
		notesText.append("</ol>");
		String returnText = notesText.toString();
		if (returnText.length() > 25000) {
			returnText = returnText.substring(0, 25000) + " more data was truncated";
		}
		return returnText;
	}
}
