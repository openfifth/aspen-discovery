-- AI-generated, human-tested seeding scripts. DEV USE ONLY
-- Run this script with mysql -u root -h aspen-db < ./seeds/dev/native-events.sql
-- Aspen Discovery: Event Mock Data Script (Idempotent & Registration Enabled)
-- Targets: MariaDB on aspen-db container
SET @libraryId = 8;
SET @locationId = 8;

-- 1. Create Fields
INSERT IGNORE INTO event_field (name, description, type, allowableValues, facetName, fieldUse) VALUES
('Primary Topic', 'The main theme of the event', 3, 'Crafts\nTechnology\nReading\nScience\nOutreach', 3, 1),
('Target Age', 'Who is this event for?', 3, 'Toddlers\nKids\nTeens\nAdults\nSeniors', 1, 1),
('Speaker Name', 'Guest speaker or facilitator', 0, '', 0, 1), 
('Equipment Provided', 'Is any equipment provided?', 2, '', 0, 1),
('Emergency Contact', 'Phone number for emergencies', 0, '', 0, 2);

-- 2. Create Field Sets
INSERT IGNORE INTO event_field_set (name, fieldSetUse) VALUES
('Standard Event Info', 1),
('Lecture Info', 1),
('Registration Form', 2);

-- 3. Capture IDs into variables to prevent MySQL "Table specified twice" (Error 1093)
SET @standardFieldSetId = (SELECT id FROM event_field_set WHERE name = 'Standard Event Info' LIMIT 1);
SET @lectureFieldSetId = (SELECT id FROM event_field_set WHERE name = 'Lecture Info' LIMIT 1);
SET @registrationFieldSetId = (SELECT id FROM event_field_set WHERE name = 'Registration Form' LIMIT 1);

-- 4. Link Fields to Sets (Full metadata mapping)
INSERT INTO event_field_set_field (eventFieldId, eventFieldSetId)
SELECT f.id, @standardFieldSetId FROM event_field f 
WHERE f.name IN ('Primary Topic', 'Target Age')
AND NOT EXISTS (SELECT 1 FROM event_field_set_field WHERE eventFieldId = f.id AND eventFieldSetId = @standardFieldSetId);

INSERT INTO event_field_set_field (eventFieldId, eventFieldSetId)
SELECT f.id, @lectureFieldSetId FROM event_field f 
WHERE f.name IN ('Primary Topic', 'Target Age', 'Speaker Name', 'Equipment Provided')
AND NOT EXISTS (SELECT 1 FROM event_field_set_field WHERE eventFieldId = f.id AND eventFieldSetId = @lectureFieldSetId);

INSERT INTO event_field_set_field (eventFieldId, eventFieldSetId)
SELECT f.id, @registrationFieldSetId FROM event_field f 
WHERE f.name = 'Emergency Contact'
AND NOT EXISTS (SELECT 1 FROM event_field_set_field WHERE eventFieldId = f.id AND eventFieldSetId = @registrationFieldSetId);

-- 5. Event Types
INSERT IGNORE INTO event_type (eventInformationFieldSetId, eventRegistrationFieldSetId, title, description, eventLength, titleCustomizable, descriptionCustomizable, lengthCustomizable) VALUES
(@standardFieldSetId, 0, 'Storytime', 'Weekly reading and fun for little ones.', 0.5, 1, 1, 1),
(@lectureFieldSetId, 0, 'Lecture', 'Academic and professional talks.', 1.0, 1, 1, 1),
(@standardFieldSetId, @registrationFieldSetId, 'Workshop', 'Hands-on creative sessions.', 2.0, 1, 1, 1),
(@standardFieldSetId, @registrationFieldSetId, 'One-on-One', 'Individualized attention.', 1.0, 1, 1, 1);

-- 6. Link Event Types to Library/Location
INSERT INTO event_type_library (eventTypeId, libraryId)
SELECT id, @libraryId FROM event_type t
WHERE NOT EXISTS (SELECT 1 FROM event_type_library WHERE eventTypeId = t.id AND libraryId = @libraryId);

INSERT INTO event_type_location (eventTypeId, locationId)
SELECT id, @locationId FROM event_type t
WHERE NOT EXISTS (SELECT 1 FROM event_type_location WHERE eventTypeId = t.id AND locationId = @locationId);

-- 7. Create 12 Varied Events (Waiting List Enabled AND Number of Seats Set)
INSERT INTO event (eventTypeId, locationId, title, description, startDate, startTime, eventLength, recurrenceOption, recurrenceCount, private, registrationRequired, numberOfSeats, waitingList, waitingListNumberOfSeats) VALUES
((SELECT id FROM event_type WHERE title = 'Storytime' LIMIT 1), @locationId, 'Baby Bounce & Rhyme', 'Songs and rhymes for infants.', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', 30, 3, 10, 0, 0, NULL, 0, NULL),
((SELECT id FROM event_type WHERE title = 'Storytime' LIMIT 1), @locationId, 'Preschool Story Hour', 'Stories, movement, and a craft.', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', 60, 3, 10, 0, 0, NULL, 0, NULL),
((SELECT id FROM event_type WHERE title = 'One-on-One' LIMIT 1), @locationId, 'Tech Help: Mobile Devices', '1-seat spot for phone help.', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', 60, 3, 8, 0, 1, 1, 1, 10),
((SELECT id FROM event_type WHERE title = 'One-on-One' LIMIT 1), @locationId, 'Career Coaching', '1-seat spot for resumes.', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '11:00:00', 60, 3, 8, 0, 1, 1, 1, 10),
((SELECT id FROM event_type WHERE title = 'Workshop' LIMIT 1), @locationId, '3D Printing Lab', 'Advanced CAD design sessions.', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', 120, 3, 6, 0, 1, 8, 1, 5),
((SELECT id FROM event_type WHERE title = 'Workshop' LIMIT 1), @locationId, 'LEGO Club', 'Free build for all ages.', DATE_ADD(CURDATE(), INTERVAL 6 DAY), '15:30:00', 60, 3, 12, 0, 1, 20, 1, 20),
((SELECT id FROM event_type WHERE title = 'Lecture' LIMIT 1), @locationId, 'Sustainable Gardening', 'Composting and more.', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '10:00:00', 60, 1, 1, 0, 1, 30, 1, 15),
((SELECT id FROM event_type WHERE title = 'Workshop' LIMIT 1), @locationId, 'Artistic Watercolor', 'Paint and relax.', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', 90, 1, 1, 0, 1, 5, 1, 5),
((SELECT id FROM event_type WHERE title = 'Lecture' LIMIT 1), @locationId, 'Local History Series', 'Exploring our local archives.', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '18:00:00', 60, 3, 5, 0, 1, 40, 1, 10),
((SELECT id FROM event_type WHERE title = 'Workshop' LIMIT 1), @locationId, 'Intro to Crochet', 'Beginner stitching.', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', 120, 1, 1, 0, 1, 12, 1, 10),
((SELECT id FROM event_type WHERE title = 'One-on-One' LIMIT 1), @locationId, 'ESL Practice', '1-on-1 language help.', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', 60, 3, 8, 0, 1, 1, 1, 5),
((SELECT id FROM event_type WHERE title = 'Workshop' LIMIT 1), @locationId, 'Makerspace Safety', 'Tool training.', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '17:00:00', 60, 3, 4, 0, 1, 5, 1, 10)
ON DUPLICATE KEY UPDATE 
    registrationRequired = VALUES(registrationRequired), 
    numberOfSeats = VALUES(numberOfSeats), 
    waitingList = VALUES(waitingList),
    waitingListNumberOfSeats = VALUES(waitingListNumberOfSeats),
    startDate = VALUES(startDate);

-- 8. Mass Instance Generator (10 Weeks of Calendar Data)
INSERT INTO event_instance (eventId, date, time, length)
SELECT e.id, DATE_ADD(e.startDate, INTERVAL (t.n * 7) DAY), e.startTime, e.eventLength
FROM event e
CROSS JOIN (SELECT 0 as n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t
WHERE e.locationId = @locationId
AND NOT EXISTS (SELECT 1 FROM event_instance WHERE eventId = e.id AND date = DATE_ADD(e.startDate, INTERVAL (t.n * 7) DAY));

-- 9. Set Topic Facet Metadata
INSERT INTO event_event_field (eventId, eventFieldId, value)
SELECT e.id, (SELECT id FROM event_field WHERE name = 'Primary Topic' LIMIT 1), 
CASE 
  WHEN e.title LIKE '%Tech%' OR e.title LIKE '%3D%' THEN 'Technology'
  WHEN e.title LIKE '%Story%' OR e.title LIKE '%Rhyme%' THEN 'Reading'
  WHEN e.title LIKE '%Crochet%' OR e.title LIKE '%LEGO%' OR e.title LIKE '%Art%' THEN 'Crafts'
  WHEN e.title LIKE '%History%' OR e.title LIKE '%Gardening%' THEN 'Science'
  ELSE 'Outreach' 
END
FROM event e
WHERE NOT EXISTS (SELECT 1 FROM event_event_field WHERE eventId = e.id AND eventFieldId = (SELECT id FROM event_field WHERE name = 'Primary Topic' LIMIT 1));