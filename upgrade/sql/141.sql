delimiter //
create function getRootMG (groupID INT(10))
    RETURNS  INT(10)
BEGIN
    DECLARE parent INT;
    REPEAT
        select parentGroupID INTO parent from invMarketGroups where marketGroupID=groupID LIMIT 1;
        IF parent IS NOT NULL THEN SET groupID = parent; END IF;
    UNTIL parent IS NULL
    END REPEAT;
    RETURN groupID;
END
//
delimiter ;
