SELECT (
        SELECT MS.url
        FROM market_shares MS
        WHERE MS.id = FM_1.market_share_id
    ) AS MSIsin,
    MAX(updated_at) AS Maxime
FROM forum_messages AS FM_1
GROUP BY MSIsin
ORDER BY Maxime DESC;
