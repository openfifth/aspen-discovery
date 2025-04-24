package com.turning_leaf_technologies.events;

import java.sql.ResultSet;
import java.sql.SQLException;

class SpringshareLibCalEvent {
	private final long id;
	private final String externalId;
	private final int deleted;

	SpringshareLibCalEvent(ResultSet existingEventsRS) throws SQLException {
		this.id = existingEventsRS.getLong("id");
		this.externalId = existingEventsRS.getString("externalId");
		this.deleted = existingEventsRS.getInt("deleted");
	}

	/**
	 * Returns the internal ID of the event.
	 *
	 * @return The internal ID.
	 */
	public long getId() {
		return id;
	}

	/**
	 * Returns the external ID of the event.
	 *
	 * @return The external identifier from the external system.
	 */
	public String getExternalId() {
		return externalId;
	}

	/**
	 * Indicates whether the event has been marked as deleted.
	 *
	 * @return {@code true} if the event is deleted; {@code false} otherwise.
	 */
	public boolean isDeleted() {
		return deleted != 0;
	}

}
