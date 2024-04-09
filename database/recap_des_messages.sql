SELECT
    (SELECT MS.isin FROM market_shares MS WHERE MS.id = FM_1.market_share_id) AS MSIsin,
    -- COALESCE(FM_1.forum_message_id, FM_1.id) AS BoursoMessageID,
    -- CASE
    --     WHEN FM_1.title = "" THEN (
    --         SELECT FM_2.title
    --         FROM forum_messages FM_2
    --         WHERE FM_2.id = FM_1.forum_message_id
    --     )
    --     ELSE FM_1.title
    -- END AS Titre,
    COUNT(*) AS NombreDeReponses,
    MAX(updated_at)
FROM forum_messages AS FM_1
GROUP BY MSIsin;
