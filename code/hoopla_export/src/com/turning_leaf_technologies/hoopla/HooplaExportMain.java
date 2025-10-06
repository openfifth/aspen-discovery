package com.turning_leaf_technologies.hoopla;

import com.turning_leaf_technologies.config.ConfigUtil;
import com.turning_leaf_technologies.file.JarUtil;
import org.aspen_discovery.grouping.RecordGroupingProcessor;
import org.aspen_discovery.grouping.RemoveRecordFromWorkResult;
import com.turning_leaf_technologies.indexing.IndexingUtils;
import com.turning_leaf_technologies.logging.LoggingUtil;
import com.turning_leaf_technologies.net.NetworkUtils;
import com.turning_leaf_technologies.net.WebServiceResponse;
import org.aspen_discovery.reindexer.GroupedWorkIndexer;
import com.turning_leaf_technologies.strings.AspenStringUtils;
import com.turning_leaf_technologies.util.SystemUtils;
import org.apache.logging.log4j.Logger;
import org.ini4j.Ini;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import org.apache.commons.lang3.StringUtils;

import java.nio.charset.StandardCharsets;
import java.sql.*;
import java.time.ZonedDateTime;
import java.time.temporal.ChronoUnit;
import java.util.*;
import java.util.Date;
import java.util.zip.CRC32;

public class HooplaExportMain {
	private static Logger logger;
	private static String serverName;

	private static Ini configIni;

	private static Long startTimeForLogging;
	private static HooplaExtractLogEntry logEntry;

	private static Connection aspenConn;
	private static PreparedStatement getAllExistingHooplaItemsStmt;
	private static PreparedStatement addHooplaTitleToDB = null;
	private static PreparedStatement updateHooplaTitleInDB = null;
	private static PreparedStatement deleteHooplaItemStmt;
	private static PreparedStatement updateLastRecordProcessedStmt;
	private static PreparedStatement getLibraryHooplaSettingsStmt;
	private static PreparedStatement updateFullUpdateForLibraryStmt;
	private static PreparedStatement getHooplaEntitlementIdStmt;
	private static PreparedStatement addHooplaEntitlementStmt;
	private static PreparedStatement getHooplaEntitlementScopeStmt;
	private static PreparedStatement addHooplaEntitlementScopeStmt;
	private static PreparedStatement deleteHooplaEntitlementScopeStmt;
	private static PreparedStatement entitlementHasScopesStmt;
	private static PreparedStatement deleteHooplaEntitlementByIdStmt;
	private static PreparedStatement getExistingEntitlementsForLibraryStmt;
	private static PreparedStatement getFlexEntitlementsForLibraryStmt;
	private static PreparedStatement upsertFlexAvailabilityStmt;
	private static PreparedStatement getExistingFlexAvailabilityStmt;

	//Record grouper
	private static GroupedWorkIndexer groupedWorkIndexer;
	private static RecordGroupingProcessor recordGroupingProcessorSingleton = null;

	//Existing records
	private static HashMap<Long, HooplaTitle> existingRecords = new HashMap<>();
	private static final HashSet<Long> titlesNeedingReindex = new HashSet<>();

	private static final String HOOPLA_TYPE_INSTANT = "Instant";
	private static final String HOOPLA_TYPE_FLEX = "Flex";

	//For Checksums
	private static final CRC32 checksumCalculator = new CRC32();

	//For 32 hours catch up
	private static int numRetries32HoursAfter = 0;

	public static void main(String[] args){
		boolean extractSingleWork = false;
		String singleWorkId = null;
		String singleWorkType = null;
		String hooplaType;
		if (args.length == 0) {
			serverName = AspenStringUtils.getInputFromCommandLine("Please enter the server name");
			if (serverName.isEmpty()) {
				System.out.println("You must provide the server name as the first argument.");
				System.exit(1);
			}
			String extractSingleWorkResponse = AspenStringUtils.getInputFromCommandLine("Process a single work? (y/N)");
			if (extractSingleWorkResponse.equalsIgnoreCase("y")) {
				extractSingleWork = true;
				String extractSingleWorkType = AspenStringUtils.getInputFromCommandLine("Enter the type of work to extract (INSTANT/Flex)");
				if (extractSingleWorkType.equalsIgnoreCase("Instant")) {
					singleWorkType = "Instant";
				} else if (extractSingleWorkType.equalsIgnoreCase("Flex")) {
					singleWorkType = "Flex";
				} else {
					singleWorkType = "Instant";
				}

			}

		} else {
			serverName = args[0];
			if (args.length > 1){
				if (args[1].equalsIgnoreCase("singleWork") || args[1].equalsIgnoreCase("singleRecord")){
					extractSingleWork = true;
					if (args.length > 2) {
						hooplaType = args[2];
						if (hooplaType.equalsIgnoreCase("Instant")) {
							singleWorkType = "Instant";
						} else if (hooplaType.equalsIgnoreCase("Flex")) {
							singleWorkType = "Flex";
						} else {
							System.out.println("Invalid work type. Please enter Instant or Flex.");
							System.exit(1);
						}
						if (args.length > 3) {
							singleWorkId = args[3];
						}
					} else {
						String extractSingleWorkType = AspenStringUtils.getInputFromCommandLine("Enter the type of work to extract (INSTANT/Flex)");
						if (extractSingleWorkType.equalsIgnoreCase("Instant")) {
							singleWorkType = "Instant";
						} else if (extractSingleWorkType.equalsIgnoreCase("Flex")) {
							singleWorkType = "Flex";
						} else {
							singleWorkType = "Instant";
						}
					}
				}
			}
		}
		if (extractSingleWork && singleWorkId == null) {
			singleWorkId = AspenStringUtils.getInputFromCommandLine("Enter the id of the title to extract");
		}

		String processName = "hoopla_export";
		logger = LoggingUtil.setupLogging(serverName, processName);

		//Get the checksum of the JAR when it was started, so we can stop if it has changed.
		long myChecksumAtStart = JarUtil.getChecksumForJar(logger, processName, "./" + processName + ".jar");
		long reindexerChecksumAtStart = JarUtil.getChecksumForJar(logger, "reindexer", "../reindexer/reindexer.jar");
		long timeAtStart = new Date().getTime();

		while (true) {
			//Hoopla only needs to run once a day, so run it in cron
			Date startTime = new Date();
			startTimeForLogging = startTime.getTime() / 1000;
			logger.info(startTime + ": Starting Hoopla Export");

			// Read the base INI file to get information about the server (current directory/cron/config.ini)
			configIni = ConfigUtil.loadConfigFile("config.ini", serverName, logger);

			//Connect to the Aspen database
			aspenConn = connectToDatabase();

			//Check to see if the jar has changes before processing records, and if so, quit
			if (myChecksumAtStart != JarUtil.getChecksumForJar(logger, processName, "./" + processName + ".jar")){
				IndexingUtils.markNightlyIndexNeeded(aspenConn, logger);
				disconnectDatabase(aspenConn);
				break;
			}
			if (reindexerChecksumAtStart != JarUtil.getChecksumForJar(logger, "reindexer", "../reindexer/reindexer.jar")){
				IndexingUtils.markNightlyIndexNeeded(aspenConn, logger);
				disconnectDatabase(aspenConn);
				break;
			}

			//Start a log entry
			createDbLogEntry(startTime, aspenConn);
			logEntry.addNote("Starting extract");
			logEntry.saveResults();

			//Get a list of all existing records in the database
			loadExistingTitles();

			//Do work here
			boolean updatesRun;
			if (singleWorkId == null) {
				updatesRun = exportHooplaData();
			} else {
				exportSingleHooplaTitle(singleWorkId, singleWorkType);
				updatesRun = true;
			}
			int numChanges = logEntry.getNumChanges();

			processRecordsToReload(logEntry);

			if (recordGroupingProcessorSingleton != null) {
				recordGroupingProcessorSingleton.close();
				recordGroupingProcessorSingleton = null;
			}

			if (groupedWorkIndexer != null) {
				groupedWorkIndexer.finishIndexingFromExtract(logEntry);
				groupedWorkIndexer.close();
				groupedWorkIndexer = null;
				existingRecords = null;
			}

			if (logEntry.hasErrors()) {
				logger.error("There were errors during the export!");
			}

			logger.info("Finished exporting data " + new Date());
			long endTime = new Date().getTime();
			long elapsedTime = endTime - startTime.getTime();
			logger.info("Elapsed Minutes " + (elapsedTime / 60000));

			//Mark that indexing has finished
			logEntry.setFinished();

			if (!updatesRun) {
				//delete the log entry
				try {
					PreparedStatement deleteLogEntryStmt = aspenConn.prepareStatement("DELETE from hoopla_export_log WHERE id = " + logEntry.getLogEntryId());
					deleteLogEntryStmt.executeUpdate();
				} catch (SQLException e) {
					logger.error("Could not delete log export ", e);
				}

			}

			if (extractSingleWork) {
				disconnectDatabase(aspenConn);
				break;
			}

			//Check to see if the jar has changes, and if so, quit
			if (myChecksumAtStart != JarUtil.getChecksumForJar(logger, processName, "./" + processName + ".jar")){
				IndexingUtils.markNightlyIndexNeeded(aspenConn, logger);
				disconnectDatabase(aspenConn);
				break;
			}
			if (reindexerChecksumAtStart != JarUtil.getChecksumForJar(logger, "reindexer", "../reindexer/reindexer.jar")){
				IndexingUtils.markNightlyIndexNeeded(aspenConn, logger);
				disconnectDatabase(aspenConn);
				break;
			}
			//Check to see if it's between midnight and 1 am, and the jar has been running more than 15 hours.  If so, restart just to clean up memory.
			GregorianCalendar nowAsCalendar = new GregorianCalendar();
			Date now = new Date();
			nowAsCalendar.setTime(now);
			if (nowAsCalendar.get(Calendar.HOUR_OF_DAY) <=1 && (now.getTime() - timeAtStart) > 15 * 60 * 60 * 1000 ){
				logger.info("Ending because we have been running for more than 15 hours and it's between midnight and one AM");
				disconnectDatabase(aspenConn);
				break;
			}
			//Check memory to see if we should close
			if (SystemUtils.hasLowMemory(configIni, logger)){
				logger.info("Ending because we have low memory available");
				disconnectDatabase(aspenConn);
				break;
			}

			disconnectDatabase(aspenConn);

			//Check to see if nightly indexing is running, and if so, wait until it is done.
			if (IndexingUtils.isNightlyIndexRunning(configIni, serverName, logger)) {
				//Quit and we will restart after if finishes
				System.exit(0);
			}else {
				//Pause before running the next export (longer if we didn't get any actual changes)
				try {
					System.gc();
					if (numChanges == 0) {
						Thread.sleep(1000 * 60 * 5);
					} else {
						Thread.sleep(1000 * 60);
					}
				} catch (InterruptedException e) {
					logger.info("Thread was interrupted");
				}
			}
		}

		System.exit(0);
	}

	private static void processRecordsToReload(HooplaExtractLogEntry logEntry) {
		try {
			PreparedStatement getRecordsToReloadStmt = aspenConn.prepareStatement("SELECT * from record_identifiers_to_reload WHERE processed = 0 and type='hoopla'", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
			PreparedStatement markRecordToReloadAsProcessedStmt = aspenConn.prepareStatement("UPDATE record_identifiers_to_reload SET processed = 1 where id = ?");
			PreparedStatement getItemDetailsForRecordStmt = aspenConn.prepareStatement("SELECT UNCOMPRESS(rawResponse) as rawResponse FROM hoopla_export where hooplaId = ?", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
			ResultSet getRecordsToReloadRS = getRecordsToReloadStmt.executeQuery();
			int numRecordsToReloadProcessed = 0;
			int numInstantRecords = 0;
			int numFlexRecords = 0;
			while (getRecordsToReloadRS.next()){
				long recordToReloadId = getRecordsToReloadRS.getLong("id");
				String recordId = getRecordsToReloadRS.getString("identifier");
				long hooplaId = Long.parseLong(StringUtils.replace(recordId,"MWT", ""));
				//Regroup the record
				getItemDetailsForRecordStmt.setLong(1, hooplaId);
				ResultSet getItemDetailsForRecordRS = getItemDetailsForRecordStmt.executeQuery();
				if (getItemDetailsForRecordRS.next()){
					String rawResponse = getItemDetailsForRecordRS.getString("rawResponse");
					try {
						JSONObject itemDetails = new JSONObject(rawResponse);
						String groupedWorkId =  getRecordGroupingProcessor().groupHooplaRecord(itemDetails, hooplaId);
						//Reindex the record
						getGroupedWorkIndexer().processGroupedWork(groupedWorkId);

						markRecordToReloadAsProcessedStmt.setLong(1, recordToReloadId);
						markRecordToReloadAsProcessedStmt.executeUpdate();
						numRecordsToReloadProcessed++;
					}catch (JSONException e){
						logEntry.incErrors("Could not parse item details for record to reload " + hooplaId, e);
					}
				}else{
					//The record has likely been deleted
					logEntry.addNote("Could not get details for Hoopla record to reload " + hooplaId + " it has been deleted");
					markRecordToReloadAsProcessedStmt.setLong(1, recordToReloadId);
					markRecordToReloadAsProcessedStmt.executeUpdate();
					numRecordsToReloadProcessed++;
				}
				getItemDetailsForRecordRS.close();
			}
			if (numRecordsToReloadProcessed > 0){
				logEntry.addNote("Regrouped " + numRecordsToReloadProcessed + " records marked for reprocessing");
			}
			getRecordsToReloadRS.close();
		}catch (Exception e){
			logEntry.incErrors("Error processing records to reload ", e);
		}
	}

	private static void cleanOrphanRecords() {
		int numDeleted = 0;
		try {
			PreparedStatement getOrphanEntitlementsStmt = aspenConn.prepareStatement("SELECT id, hooplaId from hoopla_entitlements where id not in (SELECT entitlementId from hoopla_entitlement_scopes)");
			ResultSet orphanEntitlementsRS = getOrphanEntitlementsStmt.executeQuery();
			while (orphanEntitlementsRS.next()) {
				long orphanEntitlementId = orphanEntitlementsRS.getLong("id");
				long orphanHooplaId = orphanEntitlementsRS.getLong("hooplaId");
				deleteHooplaEntitlementByIdStmt.setLong(1, orphanEntitlementId);
				deleteHooplaEntitlementByIdStmt.executeUpdate();

				// Remove the record from the grouped work
				RemoveRecordFromWorkResult result = getRecordGroupingProcessor().removeRecordFromGroupedWork("hoopla", Long.toString(orphanHooplaId));
				if (result.reindexWork) {
					getGroupedWorkIndexer().processGroupedWork(result.permanentId);
				} else if (result.deleteWork) {
					getGroupedWorkIndexer().deleteRecord(result.permanentId, result.groupedWorkId);
				}
				numDeleted++;
			}
			titlesNeedingReindex.remove(orphanHooplaId);
		} catch (SQLException e) {
			logEntry.incErrors("Error cleaning orphan records", e);
			logger.error("Error cleaning orphan records", e);
		} finally {
			orphanEntitlementsRS.close();
			getOrphanEntitlementsStmt.close();
		}
		if (numDeleted > 0) {
			logEntry.addNote("Deleted " + numDeleted + " orphan records");
			logEntry.saveResults();
		}
	}

/*
	// TO DO, This will need to be upated with new global content? do we still need to delete items?
	private static void deleteItems(String hooplaType) {
		int numDeleted = 0;
		try {
			for (HooplaTitle hooplaTitle : existingRecords.values()) {
				if (hooplaTitle.getHooplaType().equalsIgnoreCase(hooplaType) && !hooplaTitle.isFoundInExport() && hooplaTitle.isActive()) {
					deleteHooplaItemStmt.setLong(1, hooplaTitle.getId());
					deleteHooplaItemStmt.executeUpdate();
					RemoveRecordFromWorkResult result = getRecordGroupingProcessor().removeRecordFromGroupedWork("hoopla", Long.toString(hooplaTitle.getHooplaId()));

					if (hooplaType.equalsIgnoreCase("Flex")){
						PreparedStatement deleteFlexAvailabilityStmt = aspenConn.prepareStatement("DELETE from hoopla_flex_availability where hooplaId = ?");
						deleteFlexAvailabilityStmt.setLong(1, hooplaTitle.getHooplaId());
						deleteFlexAvailabilityStmt.executeUpdate();
					}

					if (result.reindexWork){
						getGroupedWorkIndexer().processGroupedWork(result.permanentId);
					}else if (result.deleteWork){
						//Delete the work from solr and the database
						getGroupedWorkIndexer().deleteRecord(result.permanentId, result.groupedWorkId);
					}
					existingRecords.remove(hooplaTitle.getHooplaId());
					numDeleted++;
					logEntry.incDeleted();
				}
			}
			if (numDeleted > 0) {
				logEntry.saveResults();
				logger.warn("Deleted " + numDeleted + " old " + hooplaType + " titles");
			}
		}catch (SQLException e) {
			logger.error("Error deleting " + hooplaType + " items", e);
			logEntry.addNote("Error deleting " + hooplaType + " items " + e);
		}
	}*/

	private static void loadExistingTitles() {
		try {
			if (existingRecords == null) existingRecords = new HashMap<>();
			ResultSet allRecordsRS = getAllExistingHooplaItemsStmt.executeQuery();
			while (allRecordsRS.next()) {
				long hooplaId = allRecordsRS.getLong("hooplaId");
				HooplaTitle newTitle = new HooplaTitle(
						allRecordsRS.getLong("id"),
						hooplaId,
						allRecordsRS.getLong("rawChecksum"),
						allRecordsRS.getLong("rawResponseLength")
				);
				existingRecords.put(hooplaId, newTitle);
			}
			allRecordsRS.close();
			//noinspection UnusedAssignment
			allRecordsRS = null;
			getAllExistingHooplaItemsStmt.close();
			getAllExistingHooplaItemsStmt = null;
		} catch (SQLException e) {
			logger.error("Error loading existing titles", e);
			logEntry.addNote("Error loading existing titles" + e);
			System.exit(-1);
		}
	}

	private static void createDbLogEntry(Date startTime, Connection aspenConn) {
		//Remove log entries older than 45 days
		long earliestLogToKeep = (startTime.getTime() / 1000) - (60 * 60 * 24 * 45);
		try {
			int numDeletions = aspenConn.prepareStatement("DELETE from hoopla_export_log WHERE startTime < " + earliestLogToKeep).executeUpdate();
			logger.info("Deleted " + numDeletions + " old log entries");
		} catch (SQLException e) {
			logger.error("Error deleting old log entries", e);
		}

		logEntry = new HooplaExtractLogEntry(aspenConn, logger);
	}

	private static boolean exportHooplaData() {
		boolean updatesRun = false;
		try{
			PreparedStatement getSettingsStmt = aspenConn.prepareStatement("SELECT * from hoopla_settings");
			ResultSet getSettingsRS = getSettingsStmt.executeQuery();
			int numSettings = 0;
			boolean globalContentUpdated = false;
			while (getSettingsRS.next()) {
				HooplaSettings settings = new HooplaSettings(getSettingsRS);
				ArrayList<HooplaLibrarySettings> librarySettings = loadLibraryHooplaSettings(settings.getSettingsId());
				numSettings++;

				// TO DO, add the check for library setting, if no library enable the neither
				// instant or flex, we dont do anything

				// Extract Global Content
				if (!globalContentUpdated) {
					globalContentUpdated = exportHooplaContent(settings);
					updatesRun |= globalContentUpdated;
				}

				updatesRun |= exportLibraryEntitlements(settings, globalContentUpdated, librarySettings);

				// Clean Orphan records
				cleanOrphanRecords();

				// Process Flex Availability
				updatesRun |= getFlexAvailability(settings, librarySettings);

				if (settings.isRegroupAllRecords()) {
					regroupAllRecords(aspenConn, settings.getSettingsId(), getGroupedWorkIndexer(), logEntry);
				}

			}
			if (numSettings == 0){
				logger.error("Unable to find settings for Hoopla, please add settings to the database");
			}
		}catch (Exception e){
			logEntry.incErrors("Error exporting hoopla data", e);
		}
		return updatesRun;
	}


	private static boolean exportHooplaContent(HooplaSettings settings) {
		boolean updatedContent = false;
		boolean doFullReload = settings.isRunFullUpdate();
		long settingsId = settings.getSettingsId();
		String hooplaAPIBaseURL = settings.getApiUrl();
		long lastUpdateOfChangedRecords = settings.getLastUpdateOfChangedRecords();
		long lastUpdateOfAllRecords = settings.getLastUpdateOfAllRecords();
		long lastUpdate = Math.max(lastUpdateOfChangedRecords, lastUpdateOfAllRecords);
		String countryCode = settings.getCountryCode();
		String lastRecordProcessed = settings.getLastRecordProcessed() != null ? settings.getLastRecordProcessed() : "0";
		int recordExtractionBatchSize = settings.getRecordExtractionBatchSize();
		int numRecordsToExtract = 0;

		String accessToken = settings.getAccessToken();
		long tokenExpirationTime = settings.getTokenExpirationTime();
		int indexingTime = settings.getIndexingTime();

		if (accessToken == null || tokenExpirationTime < (System.currentTimeMillis() / 1000)) {
			accessToken = getAccessToken(settings);
		}

		if (accessToken == null) {
			logEntry.incErrors("Could not load access token");
			return false;
		}

		logEntry.addNote("Starting global content extraction using a batch size of " + recordExtractionBatchSize + " at hour " + indexingTime);
		logEntry.saveResults();

		try {
			if (doFullReload){
				//Unset that a full update needs to be done
				PreparedStatement updateSettingsStmt = aspenConn.prepareStatement("UPDATE hoopla_settings set runFullUpdate = 0 where id = ?");
				updateSettingsStmt.setLong(1, settingsId);
				updateSettingsStmt.executeUpdate();
				logEntry.addNote("Processing full update for global content");
			} else {
				//We only want to index once a day at 1 am Local Time
				ZonedDateTime nowLocalTime = ZonedDateTime.now();
				int curHour = nowLocalTime.getHour();
				ZonedDateTime startOfToday = nowLocalTime.truncatedTo(ChronoUnit.DAYS);
				long startOfTodaySeconds = startOfToday.toEpochSecond();
				ZonedDateTime thirtyTwoHoursAgoTime = nowLocalTime.minusHours(32);
				long thirtyTwoHoursAgo = thirtyTwoHoursAgoTime.toInstant().getEpochSecond();

				if (curHour == indexingTime){
					if (lastUpdateOfChangedRecords >= startOfTodaySeconds) {
						logger.warn("Already completed today's global content extraction at " + indexingTime + ". Skipping until tomorrow.");
						return false;
					}
					//Set the last update time to 32 hours ago (go bigger to get more updates)
					if (thirtyTwoHoursAgo < lastUpdate){
						lastUpdate = thirtyTwoHoursAgo;
					}
					numRetries32HoursAfter = 0;
					logEntry.addNote("Starting daily global content extraction");
				}else{
					//It's not configured indexing time, skip for now.
					//Figure out when we last indexed this collection.
					if (lastUpdate >= thirtyTwoHoursAgo) {
						//Do not index unless it has been 32 hours
						return false;
					}
					// If we don't have updates for 32 hours, we will try 3 times
					// If we exceed 3 times and fail, we will wait until configured indexing time
					if (numRetries32HoursAfter >= 3){
						logger.warn("Exceeded 3 retries for 32 hours catch up, waiting until next indexing time at " + indexingTime);
						return false;
					}
					numRetries32HoursAfter++;
					logEntry.addNote("Retrying global content extraction after 32 hours " + numRetries32HoursAfter + " of 3");
				}
			}

			updatedContent = true;

			//Formulate the first call depending on if we are doing a full reload or not
			String startToken = lastRecordProcessed;
			String url = hooplaAPIBaseURL + "/api/v1/global-contents?countryCodes=" + countryCode;

			if (!doFullReload && lastUpdate > 0) {
				//Give a 2-minute buffer for the extract
				lastUpdate -= 120;
				logEntry.addNote("Extracting records since " + new Date(lastUpdate * 1000));
				url += "&startTime=" + lastUpdate + "&limit=" + recordExtractionBatchSize;
			} else {
				url += "&limit=" + recordExtractionBatchSize;
			}

			@SuppressWarnings("DuplicatedCode")
			HashMap<String, String> headers = new HashMap<>();
			headers.put("Authorization", "Bearer " + accessToken);
			headers.put("Content-Type", "application/json");
			headers.put("Accept", "application/json");

			PreparedStatement updateLastRecordProcessedStmt = aspenConn.prepareStatement("UPDATE hoopla_settings set lastRecordProcessed = ? where id = ?");
			int numTries = 0;
			WebServiceResponse response = null;

			while (startToken != null) {
				String paginationUrl = url + "&startToken=" + startToken;
				logger.error("url:" + paginationUrl);
				response = NetworkUtils.getURL(paginationUrl, logger, headers);
				if (response.isSuccess()) {
					JSONObject responseJSON = new JSONObject(response.getMessage());
					if (responseJSON.has("contents")) {
						JSONArray responseTitles = responseJSON.getJSONArray("contents");
						if (responseTitles != null && !responseTitles.isEmpty()) {
							updateTitlesInDB(responseTitles, false, doFullReload);
							numRecordsToExtract += responseTitles.length();
							updatedContent = true;
							logEntry.saveResults();
						}

						JSONObject metadataJSON = responseJSON.optJSONObject("metadata");
						if (metadataJSON != null && metadataJSON.has("nextStartToken")) {
							startToken = metadataJSON.get("nextStartToken").toString();
							try {
								updateLastRecordProcessedStmt.setString(1, startToken);
								updateLastRecordProcessedStmt.setLong(2, settingsId);
								updateLastRecordProcessedStmt.executeUpdate();
							} catch (SQLException e) {
								logEntry.incErrors("Error updating lastRecordProcessed ", e);
							}
						} else {
							startToken = null;
						}

					}
				} else {
					if (response.getResponseCode() == 401 || response.getResponseCode() == 504 || response.getResponseCode() == 503){
						numTries++;
						if (numTries >= 3){
							logEntry.incErrors("Error loading data after 3 attempts from" + url + " " + response.getResponseCode() + " " + response.getMessage());
						}else{
							try {
								Thread.sleep(1000 * 60 * 2); //Wait for 2 minutes before trying again
							} catch (InterruptedException e) {
								logEntry.incErrors("Error sleeping for 2 minutes for global contents", e);
							}
							accessToken = getAccessToken(settings);
							headers.put("Authorization", "Bearer " + accessToken);
						}
					}else {
						logEntry.incErrors("Error loading global content from " + url + " " + response.getResponseCode() + " " + response.getMessage());
					}
					try {
						updateLastRecordProcessedStmt.setString(1, startToken);
						updateLastRecordProcessedStmt.setLong(2, settingsId);
						updateLastRecordProcessedStmt.executeUpdate();
					} catch (SQLException e) {
						logEntry.incErrors("Error updating lastRecordProcessed ", e);
					}
					startToken = null;
				}
			}
			updateLastRecordProcessedStmt.close();
			logEntry.saveResults();
			logEntry.addNote("Completed " + numRecordsToExtract + " global content updates");
			logEntry.saveResults();

		} catch (SQLException e) {
			logEntry.incErrors("Error updating settings", e);
		}
		return updatedContent;
	}

	private static boolean exportLibraryEntitlements(HooplaSettings settings, boolean globalContentUpdated, ArrayList<HooplaLibrarySettings> librarySettings) {
		logEntry.addNote("Starting library entitlements extraction");
		logEntry.saveResults();

		if (librarySettings.isEmpty()) {
			return false;
		}

		boolean hasUpdates = false;
		for (HooplaLibrarySettings librarySetting : librarySettings) {
			boolean libraryUpdates = false;
			// Skip if the library doesn't have a Hoopla library ID
			if (!librarySetting.hasHooplaLibraryId()) {
				continue;
			}
			// Skip if the library doesn't have instant or flex enabled
			if (!librarySetting.isInstantEnabled() && !librarySetting.isFlexEnabled()) {
				continue;
			}

			boolean runFullUpdateForLibrary = settings.isRunFullUpdate() || librarySetting.isfullUpdateForLibrary();

			// Export Instant Entitlements only when running full update for library,
			// or when global contents get extracted (Full Update or Incremental Updates)
			if (librarySetting.isInstantEnabled() && (runFullUpdateForLibrary || globalContentUpdated)) {
				libraryUpdates |= exportLibraryEntitlementsForType(settings, librarySetting, HOOPLA_TYPE_INSTANT, runFullUpdateForLibrary);
			}

			// Export Flex Entitlements only when running full update for library,
			// or when global contents get extracted (Full Update or Incremental Updates)
			if (librarySetting.isFlexEnabled() && (runFullUpdateForLibrary || globalContentUpdated)) {
				libraryUpdates |= exportLibraryEntitlementsForType(settings, librarySetting, HOOPLA_TYPE_FLEX, runFullUpdateForLibrary);
			}

			if (libraryUpdates && librarySetting.isfullUpdateForLibrary()) {
				try {
					updateFullUpdateForLibraryStmt.setLong(1, librarySetting.getId());
					updateFullUpdateForLibraryStmt.executeUpdate();
				} catch (SQLException e) {
					logEntry.incErrors("Unable to update fullUpdateForLibrary for library setting " + librarySetting.getLibraryId(), e);
				}
			}

			if (libraryUpdates) {
				hasUpdates = true;
			}
		}

		// Update the timestamp for the settings after exporting all the library entitlements
		try{
			PreparedStatement updateSettingsStmt = null;
			if (settings.isRunFullUpdate()){
				if (!logEntry.hasErrors()) {
					updateSettingsStmt = aspenConn.prepareStatement("UPDATE hoopla_settings set lastUpdateOfAllRecords = ?, lastRecordProcessed = 0 where id = ?");
				} else {
					//force another full update
					PreparedStatement reactiveFullUpdateStmt = aspenConn.prepareStatement("UPDATE hoopla_settings set runFullUpdate = 1 where id = ?");
					reactiveFullUpdateStmt.setLong(1, settings.getSettingsId());
					reactiveFullUpdateStmt.executeUpdate();
				}
			}else{
				// Update the lastUpdateOfChangedRecords only if global content was updated
				if (globalContentUpdated){
					updateSettingsStmt = aspenConn.prepareStatement("UPDATE hoopla_settings set lastUpdateOfChangedRecords = ? where id = ?");
				}
			}
			if (updateSettingsStmt != null) {
				updateSettingsStmt.setLong(1, startTimeForLogging);
				updateSettingsStmt.setLong(2, settings.getSettingsId());
				updateSettingsStmt.executeUpdate();
				updateSettingsStmt.close();
				numRetries32HoursAfter = 0;
			}
		} catch (SQLException e) {
			logEntry.incErrors("Error updating settings timestamp", e);
		}

		return hasUpdates;
	}

	private static boolean exportLibraryEntitlementsForType(HooplaSettings settings, HooplaLibrarySettings librarySetting, String hooplaType, boolean runFullUpdateForLibrary) {
		boolean updateEntitlements = false;
		String hooplaLibraryId = librarySetting.getHooplaLibraryId();
		if (hooplaLibraryId == null || hooplaLibraryId.isEmpty()) {
			return false;
		}
		String accessToken = settings.getAccessToken();
		if (accessToken == null || settings.getTokenExpirationTime() < (System.currentTimeMillis() / 1000)) {
			accessToken = getAccessToken(settings);
		}
		if (accessToken == null) {
			return false;
		}

		int recordExtractionBatchSize = settings.getRecordExtractionBatchSize();
		long lastUpdateOfChangedRecords = settings.getLastUpdateOfChangedRecords();
		long lastUpdateOfAllRecords = settings.getLastUpdateOfAllRecords();
		long lastUpdate = Math.max(lastUpdateOfChangedRecords, lastUpdateOfAllRecords);
		String hooplaAPIBaseURL = settings.getApiUrl();

		String url = hooplaAPIBaseURL + "/api/v1/libraries/" + hooplaLibraryId + "/entitlements?purchaseModel=" + hooplaType + "&limit=" + recordExtractionBatchSize;

		if (!runFullUpdateForLibrary && lastUpdate > 0) {
			url += "&startTime=" + lastUpdate;
		} else {
			// Full update for library, only get active entitlements
			url += "&status=active";
		}

		// Load existing entitlements for library when running a full update
		HashMap<Long, Long> existingEntitlements = runFullUpdateForLibrary ? loadExistingEntitlementsForLibrary(librarySetting.getLibraryId(), hooplaType) : null;

		HashMap<String, String> headers = new HashMap<>();
		headers.put("Authorization", "Bearer " + accessToken);
		headers.put("Content-Type", "application/json");
		headers.put("Accept", "application/json");

		String startToken = "0";
		int numEntitlements = 0;
		int numTries = 0;

		while (startToken != null) {
			String paginationUrl = url + "&startToken=" + startToken;
			logger.error("entitlement url: " + paginationUrl);
			WebServiceResponse response = NetworkUtils.getURL(paginationUrl, logger, headers);

			if (response.isSuccess()) {
				JSONObject responseJSON = new JSONObject(response.getMessage());
				JSONArray entitlements = responseJSON.getJSONArray("entitlements");
				if (entitlements != null && !entitlements.isEmpty()) {
					numEntitlements += entitlements.length();
					updateEntitlementsInDB(entitlements, existingEntitlements, runFullUpdateForLibrary, hooplaType, librarySetting.getLibraryId());
					updateEntitlements = true;
				}

				JSONObject metadataJSON = responseJSON.optJSONObject("metadata");
				if (metadataJSON != null && metadataJSON.has("nextStartToken")) {
					startToken = metadataJSON.get("nextStartToken").toString();
				} else {
					startToken = null;
				}

			} else {
				if (response.getResponseCode() == 401 || response.getResponseCode() == 504 || response.getResponseCode() == 503){
					numTries++;
					if (numTries >= 3){
						logEntry.incErrors("Error loading entitlements after 3 attempts from" + url + " " + response.getResponseCode() + " " + response.getMessage());
					}else{
						try {
							Thread.sleep(1000 * 60 * 2); //Wait for 2 minutes before trying again
						} catch (InterruptedException e) {
							logEntry.incErrors("Error sleeping for 2 minutes for entitlments", e);
						}
						accessToken = getAccessToken(settings);
						headers.put("Authorization", "Bearer " + accessToken);
					}
				}else {
					logEntry.incErrors("Error loading entitlements from" + url + " " + response.getResponseCode() + " " + response.getMessage());
				}
				startToken = null;
			}

		}
		// Clean up the entitlements that are no longer in the API response
		if (runFullUpdateForLibrary && existingEntitlements != null && !existingEntitlements.isEmpty()) {
			for (Map.Entry<Long, Long> staleEntitlement : existingEntitlements.entrySet()) {
				Long hooplaId = staleEntitlement.getKey();
				Long entitlementId = staleEntitlement.getValue();
				try {
					deleteHooplaEntitlementScopeStmt.setLong(1, entitlementId);
					deleteHooplaEntitlementScopeStmt.setLong(2, librarySetting.getLibraryId());
					deleteHooplaEntitlementScopeStmt.executeUpdate();
				} catch (SQLException e) {
					logEntry.incErrors("Error deleting Hoopla entitlement scope for stale title " + hooplaId + " (library " + librarySetting.getLibraryId() + ")", e);
					continue;
				}
				titlesNeedingReindex.add(hooplaId);
			}
		}

		logEntry.addNote("Exported " + numEntitlements + " " + hooplaType + " entitlements for library " + librarySetting.getLibraryId());
		logEntry.saveResults();
		return updateEntitlements;
	}

	private static ArrayList<HooplaLibrarySettings> loadLibraryHooplaSettings(long settingsId) {
		ArrayList<HooplaLibrarySettings> librarySettings = new ArrayList<>();
		try {
			getLibraryHooplaSettingsStmt.setLong(1, settingsId);
			ResultSet librarySettingsRS = getLibraryHooplaSettingsStmt.executeQuery();
			while (librarySettingsRS.next()) {
				librarySettings.add(new HooplaLibrarySettings(librarySettingsRS));
			}
			librarySettingsRS.close();
		} catch (SQLException e) {
			logEntry.incErrors("Error loading Hoopla library settings for settingsId " + settingsId, e);
		}
		return librarySettings;
	}

	private static HashMap<Long, Long> loadExistingEntitlementsForLibrary(long scopeLibraryId, String hooplaType) {
		HashMap<Long, Long> existingEntitlements = new HashMap<>();
		try {
			getExistingEntitlementsForLibraryStmt.setLong(1, scopeLibraryId);
			getExistingEntitlementsForLibraryStmt.setString(2, hooplaType);
			ResultSet existingEntitlementsRS = getExistingEntitlementsForLibraryStmt.executeQuery();
			while (existingEntitlementsRS.next()) {
				existingEntitlements.put(
					existingEntitlementsRS.getLong("hooplaId"),
					existingEntitlementsRS.getLong("entitlementId")
				);
			}
			existingEntitlementsRS.close();
		} catch (SQLException e) {
			logEntry.incErrors("Error loading existing Hoopla entitlements for library " + scopeLibraryId + " (" + hooplaType + ")", e);
		}
		return existingEntitlements;
	}

	private static ArrayList<Long> loadFlexEntitlementsForLibrary(long scopeLibraryId) {
		ArrayList<Long> flexEntitlements = new ArrayList<>();
		try {
			getFlexEntitlementsForLibraryStmt.setLong(1, scopeLibraryId);
			getFlexEntitlementsForLibraryStmt.setString(2, HOOPLA_TYPE_FLEX);
			ResultSet flexEntitlementsRS = getFlexEntitlementsForLibraryStmt.executeQuery();
			while (flexEntitlementsRS.next()) {
				flexEntitlements.add(flexEntitlementsRS.getLong("hooplaId"));
			}
			flexEntitlementsRS.close();
		} catch (SQLException e) {
			logEntry.incErrors("Error loading Flex entitlements for library " + scopeLibraryId, e);
		}
		return flexEntitlements;
	}

	private static void updateFlexAvailabilityInDB(JSONArray availabilityArray, long scopeLibraryId) {
		for (int i = 0; i < availabilityArray.length(); i++) {
			try {
				JSONObject availabilityInfo = availabilityArray.getJSONObject(i);
				if (!availabilityInfo.has("contentId")) {
					logEntry.incErrors("Flex availability response missing contentId for library " + scopeLibraryId);
					continue;
				}
				long hooplaId = availabilityInfo.getLong("contentId");
				if (!availabilityInfo.has("availability")) {
					continue;
				}
				JSONObject availability = availabilityInfo.getJSONObject("availability");
				if (availability.length() > 0) {
					String status = availability.getString("status");
					int holdsQueueSize = status.equals("BORROW") ? 0 : availability.getInt("holdsQueueSize");
					int availableCopies = availability.getInt("availableCopies");
					int totalCopies = availability.getInt("totalCopies");

					boolean availabilityChanged = false;
					try {
						getExistingFlexAvailabilityStmt.setLong(1, hooplaId);
						getExistingFlexAvailabilityStmt.setLong(2, scopeLibraryId);
						ResultSet existingAvailabilityRS = getExistingFlexAvailabilityStmt.executeQuery();
						if (existingAvailabilityRS.next()) {
							int existingholdsQueueSize = existingAvailabilityRS.getInt("holdsQueueSize");
							int existingAvailableCopies = existingAvailabilityRS.getInt("availableCopies");
							int existingTotalCopies = existingAvailabilityRS.getInt("totalCopies");
							String existingStatus = existingAvailabilityRS.getString("status");
							if (existingholdsQueueSize != holdsQueueSize || existingAvailableCopies != availableCopies || existingTotalCopies != totalCopies || !Objects.equals(existingStatus, status)) {
								availabilityChanged = true;
							}
						} else {
							availabilityChanged = true;
						}
						existingAvailabilityRS.close();

						if (availabilityChanged) {
							upsertFlexAvailabilityStmt.setLong(1, hooplaId);
							upsertFlexAvailabilityStmt.setLong(2, scopeLibraryId);
							upsertFlexAvailabilityStmt.setInt(3, holdsQueueSize);
							upsertFlexAvailabilityStmt.setInt(4, availableCopies);
							upsertFlexAvailabilityStmt.setInt(5, totalCopies);
							upsertFlexAvailabilityStmt.setString(6, status);
							upsertFlexAvailabilityStmt.executeUpdate();
							titlesNeedingReindex.add(hooplaId);
						}
					} catch (SQLException e) {
						logEntry.incErrors("Error updating Flex availability for title " + hooplaId + " (library " + scopeLibraryId + ")", e);
					}
				}
			} catch (JSONException e) {
				logEntry.incErrors("Error parsing Flex availability JSON for library " + scopeLibraryId, e);
			}
		}
	}

	private static void updateEntitlementsInDB(JSONArray entitlements, HashMap<Long, Long> existingEntitlements, boolean runFullUpdateForLibrary, String hooplaType, long scopeLibraryId) {
		for (int i = 0; i < entitlements.length(); i++) {
			try {
				JSONObject entitlement = entitlements.getJSONObject(i);
				long hooplaId = entitlement.getLong("contentId");
				Long entitlementId = null;
				try {
					getHooplaEntitlementIdStmt.setLong(1, hooplaId);
					getHooplaEntitlementIdStmt.setString(2, hooplaType);
					ResultSet entitlementRS = getHooplaEntitlementIdStmt.executeQuery();
					if (!entitlementRS.next()) {
						addHooplaEntitlementStmt.setLong(1, hooplaId);
						addHooplaEntitlementStmt.setString(2, hooplaType);
						addHooplaEntitlementStmt.executeUpdate();
						ResultSet generatedKeys = addHooplaEntitlementStmt.getGeneratedKeys();
						if (generatedKeys.next()) {
							entitlementId = generatedKeys.getLong(1);
						}
					} else {
						entitlementId = entitlementRS.getLong("id");
					}
					entitlementRS.close();
				} catch (SQLException e) {
					logEntry.incErrors("Error inserting Hoopla entitlement for title " + hooplaId + " (" + hooplaType + ")", e);
				}

				if (runFullUpdateForLibrary) {
					try {
						getHooplaEntitlementScopeStmt.setLong(1, entitlementId);
						getHooplaEntitlementScopeStmt.setLong(2, scopeLibraryId);
						ResultSet scopeRS = getHooplaEntitlementScopeStmt.executeQuery();
						if (!scopeRS.next()) {
							try {
								addHooplaEntitlementScopeStmt.setLong(1, entitlementId);
								addHooplaEntitlementScopeStmt.setLong(2, scopeLibraryId);
								addHooplaEntitlementScopeStmt.executeUpdate();
							} catch (SQLException e) {
								logEntry.incErrors("Error inserting Hoopla entitlement scope for title " + hooplaId + " (library " + scopeLibraryId + ")", e);
							}
						}
					} catch (SQLException e) {
						logEntry.incErrors("Error checking existing entitlement scope for title " + hooplaId + " (library " + scopeLibraryId + ")" + " (" + hooplaType + ")", e);
					}

					if (existingEntitlements != null) {
						existingEntitlements.remove(hooplaId);
					}
					titlesNeedingReindex.add(hooplaId);
				} else {
					if (entitlement.getBoolean("active")) {
						try {
							getHooplaEntitlementScopeStmt.setLong(1, entitlementId);
							getHooplaEntitlementScopeStmt.setLong(2, scopeLibraryId);
							ResultSet scopeRS = getHooplaEntitlementScopeStmt.executeQuery();
							if (!scopeRS.next()) {
								try {
									addHooplaEntitlementScopeStmt.setLong(1, entitlementId);
									addHooplaEntitlementScopeStmt.setLong(2, scopeLibraryId);
									addHooplaEntitlementScopeStmt.executeUpdate();
								} catch (SQLException e) {
									logEntry.incErrors("Error inserting Hoopla entitlement scope for title " + hooplaId + " (library " + scopeLibraryId + ")" + " (" + hooplaType + ")", e);
								}
							}
						} catch (SQLException e) {
							logEntry.incErrors("Error checking existing entitlement scope for title " + hooplaId + " (library " + scopeLibraryId + ")" + " (" + hooplaType + ")", e);
						}
						titlesNeedingReindex.add(hooplaId);
					} else {
						try {
							getHooplaEntitlementScopeStmt.setLong(1, entitlementId);
							getHooplaEntitlementScopeStmt.setLong(2, scopeLibraryId);
							ResultSet scopeRS = getHooplaEntitlementScopeStmt.executeQuery();
							if (scopeRS.next()) {
								try {
									deleteHooplaEntitlementScopeStmt.setLong(1, entitlementId);
									deleteHooplaEntitlementScopeStmt.setLong(2, scopeLibraryId);
									deleteHooplaEntitlementScopeStmt.executeUpdate();
								} catch (SQLException e) {
									logEntry.incErrors("Error deleting Hoopla entitlement scope for title " + hooplaId + " (library " + scopeLibraryId + ")" + " (" + hooplaType + ")", e);
								}
							}
							scopeRS.close();
						} catch (SQLException e) {
							logEntry.incErrors("Error checking existing entitlement scope for title " + hooplaId + " (library " + scopeLibraryId + ")", e);
						}
						titlesNeedingReindex.add(hooplaId);
					}
				}
			} catch (JSONException e) {
				logEntry.incErrors("Error parsing entitlement JSON ", e);
			}
		}
	}

	private static boolean getFlexAvailability(HooplaSettings settings, ArrayList<HooplaLibrarySettings> librarySettings) {
		logEntry.addNote("Starting Flex availability update");
		logEntry.saveResults();

		if (librarySettings.isEmpty()) {
			return false;
		}

		String hooplaAPIBaseURL = settings.getApiUrl();
		String accessToken = settings.getAccessToken();
		long tokenExpirationTime = settings.getTokenExpirationTime();
		if (accessToken == null || tokenExpirationTime < (System.currentTimeMillis() / 1000)) {
			accessToken = getAccessToken(settings);
		}
		if (accessToken == null) {
			logEntry.incErrors("Could not load access token for Flex availability");
			return false;
		}

		boolean hasUpdates = false;
		for (HooplaLibrarySettings librarySetting : librarySettings) {
			// Skip if the library doesn't have flex enabled
			if (!librarySetting.isFlexEnabled() || !librarySetting.hasHooplaLibraryId()) {
				continue;
			}
			ArrayList<Long> flexTitleIds = loadFlexEntitlementsForLibrary(librarySetting.getLibraryId());
			if (flexTitleIds.isEmpty()) {
				logEntry.incErrors("No Flex entitlements found for library " + librarySetting.getLibraryId() + ", please run full update for this library");
				logEntry.saveResults();
				continue;
			}

			int numFlexTitlesProcessed = 0;
			// Hardcoded batch size of 1 for now
			int flexBatchSize = 1;
			while (numFlexTitlesProcessed < flexTitleIds.size()) {
				List<Long> flexBatch = flexTitleIds.subList(numFlexTitlesProcessed, numFlexTitlesProcessed + flexBatchSize);
				StringBuilder contentIdsString = new StringBuilder();
				for (Long hooplaId : flexBatch) {
					if (contentIdsString.length() > 0) {
						contentIdsString.append(',');
					}
					contentIdsString.append(hooplaId);
				}

				@SuppressWarnings("DuplicatedCode")
				HashMap<String, String> headers = new HashMap<>();
				headers.put("Authorization", "Bearer " + accessToken);
				headers.put("Content-Type", "application/json");
				headers.put("Accept", "application/json");

				String url = hooplaAPIBaseURL + "/api/v1/libraries/" + librarySetting.getHooplaLibraryId() + "/content/info?contentIds=" + contentIdsString;
				WebServiceResponse response = NetworkUtils.getURL(url, logger, headers);
				if (response.isSuccess()) {
					try {
						JSONArray availabilityArray = new JSONArray(response.getMessage());
						updateFlexAvailabilityInDB(availabilityArray, librarySetting.getLibraryId());
						numFlexTitlesProcessed += availabilityArray.length();
					} catch (JSONException e) {
						logEntry.incErrors("Error parsing Flex availability response for library " + librarySetting.getLibraryId(), e);
					}
				} else {
					if (response.getResponseCode() == 401 || response.getResponseCode() == 503 || response.getResponseCode() == 504) {
						try {
							Thread.sleep(1000 * 60 * 1); //Wait for 1 minutes before trying again
						} catch (InterruptedException e) {
							logEntry.incErrors("Error sleeping for 1 minutes for Flex availability", e);
						}
						accessToken = getAccessToken(settings);
						headers.put("Authorization", "Bearer " + accessToken);
					} else {
						logEntry.incErrors("Error getting Flex availability from " + url + " " + response.getResponseCode() + " " + response.getMessage());
						continue;
					}
				}
			}

			if (numFlexTitlesProcessed > 0) {
				hasUpdates = true;
				logEntry.addNote("Updated Flex availability for library " + librarySetting.getLibraryId() + " (processed " + numFlexTitlesProcessed + " titles)");
				logEntry.saveResults();
			}
			if (hasUpdates) {
				logEntry.addNote("Completed Flex availability updates.");
				logEntry.saveResults();
			}
		}
		return hasUpdates;
	}

	private static void exportSingleHooplaTitle(String singleWorkId, String singleWorkType) {
		try{
			logEntry.addNote("Doing extract of single work " + singleWorkId);
			logEntry.saveResults();
			PreparedStatement getSettingsStmt = aspenConn.prepareStatement("SELECT * from hoopla_settings");
			ResultSet getSettingsRS = getSettingsStmt.executeQuery();
			int numSettings = 0;
			while (getSettingsRS.next()) {
				numSettings++;
				HooplaSettings settings = new HooplaSettings(getSettingsRS);
				String hooplaAPIBaseURL = settings.getApiUrl();
				int hooplaLibraryId = 123;

				String accessToken = getAccessToken(settings);
				if (accessToken == null) {
					logEntry.incErrors("Could not load access token");
					return;
				}
				String url = hooplaAPIBaseURL + "/api/v1/libraries/" + hooplaLibraryId + "/content";
				long numericSingleWorkId = Long.parseLong(singleWorkId);
				if (singleWorkType.equalsIgnoreCase("Flex")) {
					url += "?limit=1&startToken=" + (numericSingleWorkId - 1) + "&purchaseModel=EST";
				} else {
					url += "?limit=1&startToken=" + (numericSingleWorkId - 1) + "&purchaseModel=PPU";
				}
				HashMap<String, String> headers = new HashMap<>();
				headers.put("Authorization", "Bearer " + accessToken);
				headers.put("Content-Type", "application/json");
				headers.put("Accept", "application/json");
				WebServiceResponse response = NetworkUtils.getURL(url, logger, headers);
				if (!response.isSuccess()){
					logEntry.incErrors("Could not get titles from " + url + " " + response.getMessage());
				}else {
					JSONObject responseJSON = new JSONObject(response.getMessage());
					if (responseJSON.has("titles")) {
						JSONArray responseTitles = responseJSON.getJSONArray("titles");
						if (responseTitles != null && !responseTitles.isEmpty()) {
							updateTitlesInDB(responseTitles, true, false);
							logEntry.saveResults();

							if (singleWorkType.equalsIgnoreCase("Flex")) {
								if (!responseTitles.isEmpty()) {
									JSONObject titleObj = responseTitles.getJSONObject(0);
									boolean isActive = titleObj.getBoolean("active");
									if (!isActive) {
										logEntry.addNote("Skipping availability check for inactive Flex title: " + numericSingleWorkId);
									} else {
										String availUrl = hooplaAPIBaseURL + "/api/v1/libraries/" + hooplaLibraryId + "/content/info?contentIds=" + numericSingleWorkId;
										WebServiceResponse availResponse = NetworkUtils.getURL(availUrl, logger, headers);
										if (!availResponse.isSuccess()) {
											logEntry.incErrors("Could not get availability for Flex title " + numericSingleWorkId + " from " + availUrl + " " + availResponse.getMessage());
										} else {
											try {
												JSONArray availabilityArray = new JSONArray(availResponse.getMessage());
												if (!availabilityArray.isEmpty()) {
													JSONObject titleInfo = availabilityArray.getJSONObject(0);
													JSONObject availability = titleInfo.getJSONObject("availability");

													// Direct update without comparing old values
													PreparedStatement updateFlexAvailabilityStmt = aspenConn.prepareStatement(
														"INSERT INTO hoopla_flex_availability (hooplaId, holdsQueueSize, " +
														"availableCopies, totalCopies, status) " +
														"VALUES (?, ?, ?, ?, ?) " +
														"ON DUPLICATE KEY UPDATE " +
														"holdsQueueSize = VALUES(holdsQueueSize), " +
														"availableCopies = VALUES(availableCopies), " +
														"totalCopies = VALUES(totalCopies), " +
														"status = VALUES(status)"
													);
													int holdsQueueSize = availability.has("holdsQueueSize") ? availability.getInt("holdsQueueSize") : 0;

													updateFlexAvailabilityStmt.setLong(1, numericSingleWorkId);
													updateFlexAvailabilityStmt.setInt(2, holdsQueueSize);
													updateFlexAvailabilityStmt.setInt(3, availability.getInt("availableCopies"));
													updateFlexAvailabilityStmt.setInt(4, availability.getInt("totalCopies"));
													updateFlexAvailabilityStmt.setString(5, availability.getString("status"));
													updateFlexAvailabilityStmt.executeUpdate();

													logEntry.addNote("Updated availability for Flex title " + numericSingleWorkId);
													logEntry.incAvailabilityChanges();
												}
											} catch (Exception e) {
												logEntry.incErrors("Error updating Flex availability for title " +
													numericSingleWorkId, e);
											}
										}
									}
								}
							}
						}
					}
				}
			}
			if (numSettings == 0){
				logger.error("Unable to find settings for Hoopla when processing single title, please add settings to the database");
			}
		}catch (Exception e){
			logEntry.incErrors("Error exporting hoopla data", e);
		}
	}

	private static void updateTitlesInDB(JSONArray responseTitles, boolean forceRegrouping, boolean doFullReload) {
		logEntry.incNumProducts(responseTitles.length());
		for (int i = 0; i < responseTitles.length(); i++){
			try {
				JSONObject curTitle = responseTitles.getJSONObject(i);

				String rawResponse = curTitle.toString();
				checksumCalculator.reset();
				checksumCalculator.update(rawResponse.getBytes());
				long rawChecksum = checksumCalculator.getValue();

				long hooplaId = curTitle.getLong("id"); //formerly titleId was used, but this is not unique for TV series

				HooplaTitle existingTitle = existingRecords.get(hooplaId);
				boolean recordUpdated = false;
				if (existingTitle != null) {
					//Record exists
					if ((existingTitle.getChecksum() != rawChecksum) || (existingTitle.getRawResponseLength() != rawResponse.length())){
						recordUpdated = true;
						logEntry.incUpdated();
					}
					existingTitle.setFoundInExport(true);
				}else{
					logEntry.incAdded();
				}

				if (existingTitle == null){
					addHooplaTitleToDB.setLong(1, hooplaId);
					addHooplaTitleToDB.setString(2, curTitle.getString("title"));
					addHooplaTitleToDB.setString(3, curTitle.getString("format"));
					addHooplaTitleToDB.setBoolean(4, curTitle.getBoolean("isParentalAdvisory"));
					addHooplaTitleToDB.setBoolean(5, curTitle.getBoolean("isDemo"));
					addHooplaTitleToDB.setBoolean(6, curTitle.getBoolean("containsProfanity"));
					if (curTitle.has("ratings")) {
						JSONArray ratingsArray = curTitle.getJSONArray("ratings");
						if (ratingsArray.length() > 0) {
							addHooplaTitleToDB.setString(7, ratingsArray.getJSONObject(0).getString("ratingValue"));
						} else {
							addHooplaTitleToDB.setString(7, "");
						}
					} else {
						addHooplaTitleToDB.setString(7, "");
					}
					addHooplaTitleToDB.setBoolean(8, curTitle.getBoolean("isAbridged"));
					addHooplaTitleToDB.setBoolean(9, curTitle.getBoolean("isForChildren"));
					if (curTitle.has("ppuPrices")) {
						JSONArray ppuPricesArray = curTitle.getJSONArray("ppuPrices");
						if (ppuPricesArray.length() > 0) {
							addHooplaTitleToDB.setDouble(10, ppuPricesArray.getJSONObject(0).getDouble("ppuPrice"));
						} else {
							addHooplaTitleToDB.setDouble(10, 0.0);
						}
					} else {
						addHooplaTitleToDB.setDouble(10, 0.0);
					}
					addHooplaTitleToDB.setLong(11, rawChecksum);
					addHooplaTitleToDB.setString(12, rawResponse);
					addHooplaTitleToDB.setLong(13, startTimeForLogging);
					try {
						addHooplaTitleToDB.executeUpdate();
					}catch (DataTruncation e) {
						logEntry.addNote("Record " + hooplaId + " " + curTitle.getString("title") + " contained invalid data " + e);
					}catch (SQLException e){
						logEntry.incErrors("Error adding hoopla title to database record " + hooplaId + " " + curTitle.getString("title"), e);
					}
				}else if (recordUpdated || doFullReload || forceRegrouping){
					updateHooplaTitleInDB.setString(1, curTitle.getString("title"));
					updateHooplaTitleInDB.setString(2, curTitle.getString("format"));
					updateHooplaTitleInDB.setBoolean(3, curTitle.getBoolean("isParentalAdvisory"));
					updateHooplaTitleInDB.setBoolean(4, curTitle.getBoolean("isDemo"));
					updateHooplaTitleInDB.setBoolean(5, curTitle.getBoolean("containsProfanity"));
					if (curTitle.has("ratings")) {
						JSONArray ratingsArray = curTitle.getJSONArray("ratings");
						if (ratingsArray.length() > 0) {
							updateHooplaTitleInDB.setString(6, ratingsArray.getJSONObject(0).getString("ratingValue"));
						} else {
							updateHooplaTitleInDB.setString(6, "");
						}
					} else {
						updateHooplaTitleInDB.setString(6, "");
					}
					updateHooplaTitleInDB.setBoolean(7, curTitle.getBoolean("isAbridged"));
					updateHooplaTitleInDB.setBoolean(8, curTitle.getBoolean("isForChildren"));
					if (curTitle.has("ppuPrices")) {
						JSONArray ppuPricesArray = curTitle.getJSONArray("ppuPrices");
						if (ppuPricesArray.length() > 0) {
							updateHooplaTitleInDB.setDouble(9, ppuPricesArray.getJSONObject(0).getDouble("ppuPrice"));
						} else {
							updateHooplaTitleInDB.setDouble(9, 0.0);
						}
					} else {
						updateHooplaTitleInDB.setDouble(9, 0.0);
					}
					updateHooplaTitleInDB.setLong(10, rawChecksum);
					updateHooplaTitleInDB.setString(11, rawResponse);
					updateHooplaTitleInDB.setLong(12, existingTitle.getId());

					try {
						updateHooplaTitleInDB.executeUpdate();
					}catch (DataTruncation e) {
						logEntry.addNote("Record " + hooplaId + " " + curTitle.getString("title") + " contained invalid data " + e);
					}catch (SQLException e){
						logEntry.incErrors("Error updating hoopla data in database for record " + hooplaId + " " + curTitle.getString("title"), e);
					}
				}

			}catch (Exception e){
				logEntry.incErrors("Error updating hoopla data in db", e);
			}
		}
	}

	private static void indexRecord(String groupedWorkId) {
		getGroupedWorkIndexer().processGroupedWork(groupedWorkId);
	}

	private static String getAccessToken(HooplaSettings settings) {
		String username = settings.getApiUsername();
		String password = settings.getApiPassword();
		if (username == null || password == null){
			logger.error("Please set HooplaAPIUser and HooplaAPIPassword in settings");
			logEntry.addNote("Please set HooplaAPIUser and HooplaAPIPassword in settings");
			return null;
		}
		int numTries = 0;
		while (numTries <= 3) {
			numTries++;
			String getTokenUrl = settings.getApiUrl() + "/v2/token";
			WebServiceResponse response = NetworkUtils.postToURL(getTokenUrl, null, "application/json", null, logger, username + ":" + password);

			if (response.isSuccess()) {
				try {
					JSONObject responseJSON = new JSONObject(response.getMessage());
					String  accessToken =  responseJSON.getString("access_token");
					long tokenExpirationTime = (System.currentTimeMillis() / 1000) + responseJSON.getLong("expires_in");

					try {
						PreparedStatement updateTokenStmt = aspenConn.prepareStatement(
							"UPDATE hoopla_settings SET accessToken = ?, tokenExpirationTime = ? WHERE id = ?"
						);
						updateTokenStmt.setString(1, accessToken);
						updateTokenStmt.setLong(2, tokenExpirationTime);
						updateTokenStmt.setLong(3, settings.getSettingsId());
						updateTokenStmt.executeUpdate();
						return accessToken;
					} catch (SQLException e) {
						logEntry.incErrors("Error storing token", e);
					}
				} catch (JSONException e) {
					if (numTries == 3) {
						logEntry.addNote("Could not parse JSON for token " + response.getMessage());
						logger.error("Could not parse JSON for token " + response.getMessage(), e);
						return null;
					} else {
						try {
							Thread.sleep(1000 * 60 * 2);
						} catch (InterruptedException ex) {
							logEntry.incErrors("Thread was interrupted while sleeping");
						}
					}
				}
			}
		}
		logEntry.addNote("Could not get access token in 3 tries");
		return null;
	}

	private static Connection connectToDatabase(){
		Connection aspenConn = null;
		try{
			String databaseConnectionInfo = ConfigUtil.cleanIniValue(configIni.get("Database", "database_aspen_jdbc"));
			if (databaseConnectionInfo != null) {
				aspenConn = DriverManager.getConnection(databaseConnectionInfo);
				getAllExistingHooplaItemsStmt = aspenConn.prepareStatement("SELECT id, hooplaId, rawChecksum, UNCOMPRESSED_LENGTH(rawResponse) as rawResponseLength from hoopla_export");
				addHooplaTitleToDB = aspenConn.prepareStatement("INSERT INTO hoopla_export (hooplaId, title, format, pa, demo, profanity, rating, abridged, children, ppuPrice, rawChecksum, rawResponse, dateFirstDetected) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,COMPRESS(?),?) ");
				updateHooplaTitleInDB = aspenConn.prepareStatement("UPDATE hoopla_export set title = ?, format = ?, pa = ?, demo = ?, profanity = ?, " +
						"rating = ?, abridged = ?, children = ?, ppuPrice = ?, rawChecksum = ?, rawResponse = COMPRESS(?) where id = ?");
				deleteHooplaItemStmt = aspenConn.prepareStatement("DELETE FROM hoopla_export where id = ?");
				getLibraryHooplaSettingsStmt = aspenConn.prepareStatement("SELECT * FROM library_hoopla_settings WHERE settingId = ?");
				updateFullUpdateForLibraryStmt = aspenConn.prepareStatement("UPDATE library_hoopla_settings SET fullUpdateForLibrary = 0 WHERE id = ?");
				getHooplaEntitlementIdStmt = aspenConn.prepareStatement("SELECT id FROM hoopla_entitlements WHERE hooplaId = ? AND hooplaType = ?");
				addHooplaEntitlementStmt = aspenConn.prepareStatement("INSERT INTO hoopla_entitlements (hooplaId, hooplaType) VALUES (?, ?)");
				getHooplaEntitlementScopeStmt = aspenConn.prepareStatement("SELECT * FROM hoopla_entitlement_scopes WHERE entitlementId = ? AND scopeLibraryId = ?");
				addHooplaEntitlementScopeStmt = aspenConn.prepareStatement("INSERT INTO hoopla_entitlement_scopes (entitlementId, scopeLibraryId) VALUES (?, ?)");
				deleteHooplaEntitlementScopeStmt = aspenConn.prepareStatement("DELETE FROM hoopla_entitlement_scopes WHERE entitlementId = ? AND scopeLibraryId = ?");
				entitlementHasScopesStmt = aspenConn.prepareStatement("SELECT count(*) FROM hoopla_entitlement_scopes WHERE entitlementId = ?");
				deleteHooplaEntitlementByIdStmt = aspenConn.prepareStatement("DELETE FROM hoopla_entitlements WHERE id = ?");
				getExistingEntitlementsForLibraryStmt = aspenConn.prepareStatement("SELECT he.id AS entitlementId, he.hooplaId FROM hoopla_entitlements he INNER JOIN hoopla_entitlement_scopes hes ON hes.entitlementId = he.id WHERE hes.scopeLibraryId = ? AND he.hooplaType = ?");
				getFlexEntitlementsForLibraryStmt = aspenConn.prepareStatement("SELECT he.hooplaId FROM hoopla_entitlements he INNER JOIN hoopla_entitlement_scopes hes ON hes.entitlementId = he.id WHERE hes.scopeLibraryId = ? AND he.hooplaType = ?");
				upsertFlexAvailabilityStmt = aspenConn.prepareStatement("INSERT INTO hoopla_flex_availability (hooplaId, scopeLibraryId, holdsQueueSize, availableCopies, totalCopies, status) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE scopeLibraryId = VALUES(scopeLibraryId), holdsQueueSize = VALUES(holdsQueueSize), availableCopies = VALUES(availableCopies), totalCopies = VALUES(totalCopies), status = VALUES(status)");
				getExistingFlexAvailabilityStmt = aspenConn.prepareStatement("SELECT holdsQueueSize, availableCopies, totalCopies, status FROM hoopla_flex_availability WHERE hooplaId = ? AND scopeLibraryId = ?");
			}else{
				logger.error("Aspen database connection information was not provided");
				System.exit(1);
			}
		}catch (Exception e){
			logger.error("Error connecting to Aspen database " + e);
			System.exit(1);
		}
		return aspenConn;
	}

	private static void disconnectDatabase(Connection aspenConn) {
		try{
			addHooplaTitleToDB.close();
			addHooplaTitleToDB = null;
			updateHooplaTitleInDB.close();
			updateHooplaTitleInDB = null;
			deleteHooplaItemStmt.close();
			deleteHooplaItemStmt = null;
			getLibraryHooplaSettingsStmt.close();
			getLibraryHooplaSettingsStmt = null;
			updateFullUpdateForLibraryStmt.close();
			updateFullUpdateForLibraryStmt = null;
			getHooplaEntitlementIdStmt.close();
			getHooplaEntitlementIdStmt = null;
			addHooplaEntitlementStmt.close();
			addHooplaEntitlementStmt = null;
			getHooplaEntitlementScopeStmt.close();
			getHooplaEntitlementScopeStmt = null;
			addHooplaEntitlementScopeStmt.close();
			addHooplaEntitlementScopeStmt = null;
			deleteHooplaEntitlementScopeStmt.close();
			deleteHooplaEntitlementScopeStmt = null;
			entitlementHasScopesStmt.close();
			entitlementHasScopesStmt = null;
			deleteHooplaEntitlementByIdStmt.close();
			deleteHooplaEntitlementByIdStmt = null;
			getExistingEntitlementsForLibraryStmt.close();
			getExistingEntitlementsForLibraryStmt = null;
			getFlexEntitlementsForLibraryStmt.close();
			getFlexEntitlementsForLibraryStmt = null;
			upsertFlexAvailabilityStmt.close();
			upsertFlexAvailabilityStmt = null;
			getExistingFlexAvailabilityStmt.close();
			getExistingFlexAvailabilityStmt = null;

			aspenConn.close();
			//noinspection UnusedAssignment
			aspenConn = null;
		}catch (Exception e){
			logger.error("Error closing database ", e);
			System.exit(1);
		}
	}

	private static GroupedWorkIndexer getGroupedWorkIndexer() {
		if (groupedWorkIndexer == null) {
			groupedWorkIndexer = new GroupedWorkIndexer(serverName, aspenConn, configIni, false, false, logEntry, logger);
		}
		return groupedWorkIndexer;
	}

	private static RecordGroupingProcessor getRecordGroupingProcessor(){
		if (recordGroupingProcessorSingleton == null) {
			recordGroupingProcessorSingleton = new RecordGroupingProcessor(aspenConn, serverName, logEntry, logger);
		}
		return recordGroupingProcessorSingleton;
	}

	private static void regroupAllRecords(Connection dbConn, long settingsId, GroupedWorkIndexer indexer, HooplaExtractLogEntry logEntry)  throws SQLException {
		logEntry.addNote("Starting to regroup all records");
		PreparedStatement getAllRecordsToRegroupStmt = dbConn.prepareStatement("SELECT hooplaId, UNCOMPRESS(rawResponse) as rawResponse from hoopla_export where active = 1", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
		//It turns out to be quite slow to look this up repeatedly, grab the existing values for all and store in memory
		PreparedStatement getOriginalPermanentIdForRecordStmt = dbConn.prepareStatement("SELECT identifier, permanent_id from grouped_work_primary_identifiers join grouped_work on grouped_work_id = grouped_work.id WHERE type = 'hoopla'", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
		HashMap<Long, String> allPermanentIdsForHoopla = new HashMap<>();
		ResultSet getOriginalPermanentIdForRecordRS = getOriginalPermanentIdForRecordStmt.executeQuery();
		while (getOriginalPermanentIdForRecordRS.next()){
			allPermanentIdsForHoopla.put(getOriginalPermanentIdForRecordRS.getLong("identifier"), getOriginalPermanentIdForRecordRS.getString("permanent_id"));
		}
		getOriginalPermanentIdForRecordRS.close();
		getOriginalPermanentIdForRecordStmt.close();
		ResultSet allRecordsToRegroupRS = getAllRecordsToRegroupStmt.executeQuery();
		while (allRecordsToRegroupRS.next()) {
			logEntry.incRecordsRegrouped();
			long recordIdentifier = allRecordsToRegroupRS.getLong("hooplaId");
			String originalGroupedWorkId;
			originalGroupedWorkId = allPermanentIdsForHoopla.get(recordIdentifier);
			if (originalGroupedWorkId == null){
				originalGroupedWorkId = "false";
			}
			String rawResponseString = new String(allRecordsToRegroupRS.getBytes("rawResponse"), StandardCharsets.UTF_8);
			JSONObject rawResponse = new JSONObject(rawResponseString);
			//Pass null to processMarcRecord.  It will do the lookup to see if there is an existing id there.
			String groupedWorkId = getRecordGroupingProcessor().groupHooplaRecord(rawResponse, recordIdentifier);
			if (!originalGroupedWorkId.equals(groupedWorkId)) {
				logEntry.incChangedAfterGrouping();
			}
			//process records to regroup after every 1000 changes, so we keep up with the changes.
			if (logEntry.getNumChangedAfterGrouping() % 1000 == 0){
				indexer.processScheduledWorks(logEntry, false, -1);
			}
		}

		//Finish reindexing anything that just changed
		if (logEntry.getNumChangedAfterGrouping() > 0){
			indexer.processScheduledWorks(logEntry, false, -1);
		}

		try {
			PreparedStatement clearRegroupAllRecordsStmt = dbConn.prepareStatement("UPDATE hoopla_settings set regroupAllRecords = 0 where id =?");
			clearRegroupAllRecordsStmt.setLong(1, settingsId);
			clearRegroupAllRecordsStmt.executeUpdate();
		}catch (Exception e){
			logEntry.incErrors("Could not clear regroup all records", e);
		}
		logEntry.addNote("Finished regrouping all records");
		logEntry.saveResults();
	}
}
