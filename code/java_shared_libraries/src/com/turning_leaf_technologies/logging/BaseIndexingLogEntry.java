package com.turning_leaf_technologies.logging;

import org.apache.logging.log4j.Logger;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;

public abstract class BaseIndexingLogEntry extends BaseLogEntry{
	protected int numInvalidRecords = 0;

	protected BaseIndexingLogEntry(Logger logger){
		super(logger);
	}

	public void incInvalidRecords(String invalidRecordId){
		this.numInvalidRecords++;
		this.addNote("Invalid Record found: " + invalidRecordId);
	}
}
