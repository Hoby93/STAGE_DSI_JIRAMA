SELECT *
    FROM zonevente
    WHERE ST_Intersects(ST_GeomFromText('POLYGON((47.46512174606323 -18.921552370778503, 47.467589378356934 -18.92153207260379, 47.467503547668464 -18.92329800458227, 47.465765476226814 -18.924292601735317, 47.464778423309326 -18.922567276371428, 47.46512174606323 -18.921552370778503))'), coord)


SELECT *
    FROM ZoneVente z
    WHERE EXISTS (
        SELECT 1
        FROM JSON_TABLE(z.coord, '$[*]' COLUMNS (
            latitude DOUBLE PATH '$.latitude',
            longitude DOUBLE PATH '$.longitude'
        )) AS coords
        WHERE coords.latitude BETWEEN -18.967563206686354 AND -18.87773208918725
        AND coords.longitude BETWEEN 47.4214021067684 AND 47.5112332242675
    );

SELECT *
FROM ZoneVente
WHERE ST_Intersects(
    coord,
    ST_MakeEnvelope(47.4214021067684, -18.967563206686354, 47.5112332242675, -18.87773208918725)
);

SELECT *
FROM zonevente
WHERE ST_Within(
    coord,
    ST_MakePolygon(
        ST_GeomFromText('LINESTRING(-18.967563206686354 47.4214021067684, 
                          47.5112332242675 47.4214021067684, 
                          47.5112332242675 -18.87773208918725, 
                          -18.967563206686354 -18.87773208918725, 
                          -18.967563206686354 47.4214021067684)')
    )
);

SELECT *
FROM ZoneVente
WHERE ST_Intersects(
    coord,
    ST_GeomFromText('POLYGON((47.4214021067684 -18.967563206686354, 47.5112332242675 -18.967563206686354, 47.5112332242675 -18.87773208918725, 47.4214021067684 -18.87773208918725, 47.4214021067684 -18.967563206686354))')
);


select 
    id_region, id_district, id_commune, id_fokontany, region.libelle as region, district.libelle as district, commune.libelle as commune, fokontany.libelle as fokontany
    from fokontany 
    join commune on commune.id_commune = fokontany.commune_id
    join district on district.id_district = commune.district_id
    join region on region.id_region = district.region_id;

select 
    id_secteur, coord 
    from v_territoire_secteur 
    where id_region = 1
    group by id_secteur;

SELECT 
    AVG(ST_X(ST_Centroid(coord))) AS avg_longitude, AVG(ST_Y(ST_Centroid(coord))) AS avg_latitude 
    FROM (select 
    id_secteur, coord 
    from v_territoire_secteur 
    where id_district = 1
    group by id_secteur) as sr;


-- Avoir le centre global de plusieur secteurs
SELECT AVG(ST_X(ST_Centroid(coord))) AS avg_longitude, AVG(ST_Y(ST_Centroid(coord))) AS avg_latitude FROM v_territoire_secteur;


SELECT 
    secteur_electrique.id_secteur,
    GROUP_CONCAT(DISTINCT v_territoire.id_region ORDER BY v_territoire.id_region SEPARATOR ';') AS id_regions,
    GROUP_CONCAT(DISTINCT v_territoire.id_district ORDER BY v_territoire.id_district SEPARATOR ';') AS id_districts,
    GROUP_CONCAT(DISTINCT v_territoire.id_commune ORDER BY v_territoire.id_commune SEPARATOR ';') AS id_communes,
    GROUP_CONCAT(DISTINCT v_territoire.id_fokontany ORDER BY v_territoire.id_fokontany SEPARATOR ';') AS id_fokontanys,
    GROUP_CONCAT(DISTINCT v_territoire.region ORDER BY v_territoire.region SEPARATOR ';') AS regions,
    GROUP_CONCAT(DISTINCT v_territoire.district ORDER BY v_territoire.district SEPARATOR ';') AS districts,
    GROUP_CONCAT(DISTINCT v_territoire.commune ORDER BY v_territoire.commune SEPARATOR ';') AS communes,
    GROUP_CONCAT(DISTINCT v_territoire.fokontany ORDER BY v_territoire.fokontany SEPARATOR ';') AS fokontanys
    -- secteur_electrique.coord
FROM fkt_secteur
JOIN v_territoire ON v_territoire.id_fokontany = fkt_secteur.fokontany_id
JOIN secteur_electrique ON secteur_electrique.id_secteur = fkt_secteur.secteur_id
GROUP BY secteur_electrique.id_secteur;

-- create view v_territoire_secteur as
select 
    v_territoire.*, id_secteur, secteur_electrique.coord
    from fkt_secteur
    join v_territoire on v_territoire.id_fokontany = fkt_secteur.fokontany_id
    join secteur_electrique on secteur_electrique.id_secteur = fkt_secteur.secteur_id;


SELECT 
    v_secteur.id_secteur, 
    v_secteur.fokontanys, 
    -- v_secteur.coord, 
    coupure.id_coupure, 
    coupure.libelle, 
    coupure.date_debut, 
    coupure.date_fin
FROM 
    v_secteur
LEFT JOIN 
    coupure_secteur ON v_secteur.id_secteur = coupure_secteur.secteur_id
LEFT JOIN 
    coupure ON coupure_secteur.coupure_id = coupure.id_coupure 
    AND DATE(coupure.date_debut) = '2024-07-30'
ORDER BY 
    v_secteur.id_secteur, 
    coupure.date_debut;



-- {P1, P2, P5}
SELECT ref_post
FROM (
    SELECT 'P1' AS ref_post
    UNION ALL
    SELECT 'P2'
    UNION ALL
    SELECT 'P5'
) AS ref_posts
WHERE ref_post NOT IN (SELECT ref_post FROM post_electrique);


INSERT INTO post_electrique (ref_post, secteur_id) ?
    SELECT ref_post, 2
    FROM (SELECT 'P6' AS ref_post UNION ALL SELECT 'P7' AS ref_post UNION ALL SELECT 'P8' AS ref_post) AS tmp 
    WHERE ref_post NOT IN ( SELECT ref_post FROM post_electrique);

select 
    infrastructure.libelle,
    fokontany.libelle as fokontany,
    commune.libelle as commune,
    district.libelle as district,
    region.libelle as region
from infrastructure
join fokontany on infrastructure.fkt_id = fokontany.id_fokontany
join commune on fokontany.commune_id = commune.id_commune
join district on commune.district_id = district.id_district
join region on district.region_id = region.id_region;


SELECT 
    fkt.id_fokontany,
    fkt.libelle AS fokontany,
    commune.libelle AS commune,
    district.libelle AS district,
    region.libelle AS region,
    COUNT(CASE WHEN infrastructure.type_infra = 1 THEN 1 END) AS nb_shop,
    COUNT(CASE WHEN infrastructure.type_infra = 2 THEN 1 END) AS nb_agence
FROM fokontany fkt
JOIN commune ON fkt.commune_id = commune.id_commune
JOIN district ON commune.district_id = district.id_district
JOIN region ON district.region_id = region.id_region
LEFT JOIN infrastructure ON fkt.id_fokontany = infrastructure.fkt_id
GROUP BY fkt.id_fokontany, fokontany, commune.libelle, district.libelle, region.libelle
ORDER BY fkt.id_fokontany;

--  avoir l'effectif(secteur, post, shop et agence) dans le pays
SELECT 
    (SELECT COUNT(*) FROM secteur_electrique) AS nb_secteur,
    (SELECT COUNT(*) FROM post_electrique) AS nb_post,
    (SELECT COUNT(*) FROM infrastructure WHERE type_infra = 1) AS nb_shop,
    (SELECT COUNT(*) FROM infrastructure WHERE type_infra = 2) AS nb_agence;


select 
    fokontany, sum(nb_shop) as nb_shop, sum(nb_agence) as nb_agence 
    from v_effectif_infra 
    where fokontany like '%%'
    group by fokontany;

select 
    libelle as region, ref_secteur, ref_post
from post_electrique
join secteur_electrique on post_electrique.secteur_id = secteur_electrique.id_secteur
join region on secteur_electrique.region_id = region.id_region;


UPDATE zone_electrique
SET ref_zone = CONCAT('Z', id_zone)
WHERE ref_zone is null;



SELECT 
    niveau,
    date_coupure,
    SEC_TO_TIME(SUM(TIME_TO_SEC(duree_coupure))) AS heure_total_coupure,
    COUNT(id_coupure) AS frequence_coupure,
    (SELECT 
            heure_coupure
            FROM v_coupure_secteur AS vcs_sub
            WHERE vcs_sub.niveau = vcs.niveau
            GROUP BY heure_coupure
            ORDER BY COUNT(*) DESC
            LIMIT 1) AS heure_coupure_frequent,
    SEC_TO_TIME(AVG(TIME_TO_SEC(duree_coupure))) AS duree_moyenne_coupure
FROM v_coupure_secteur vcs
WHERE DATE_FORMAT(date_coupure, '%Y-%m') = '2024-08'and niveau = 'S1'
GROUP BY niveau, date_coupure
ORDER BY niveau;


SELECT 
    niveau,
    DATE_FORMAT(date_coupure, '%Y-%m-%d') AS niv_date,
    SEC_TO_TIME(SUM(TIME_TO_SEC(duree_coupure))) AS heure_total_coupure,
    COUNT(id_coupure) AS frequence_coupure,
    (SELECT 
            heure_coupure
            FROM v_coupure_secteur AS vcs_sub
            WHERE vcs_sub.niveau = vcs.niveau
            GROUP BY heure_coupure
            ORDER BY COUNT(*) DESC
            LIMIT 1) AS heure_coupure_frequent,
    SEC_TO_TIME(AVG(TIME_TO_SEC(duree_coupure))) AS duree_moyenne_coupure
FROM v_coupure_secteur vcs
WHERE DATE_FORMAT(date_coupure, '%Y-%m') = '2024-08' AND niveau = 'S1'
GROUP BY niveau, niv_date 
ORDER BY niv_date asc;



INSERT INTO coupure_zone (coupure_id, zone_id)
SELECT 1, id_zone
FROM (
    SELECT * FROM ('1,2,3')
) AS t(id_zone);
    

select 
    region.libelle, secteur_electrique.* 
    from secteur_electrique
    join region on region.id_region = secteur_electrique.region_id;


select * from v_zone_coupee where date_debut >= '2024-08-16 06:00:00';

select * from v_zone_coupee
            where ref_zone like '%%'
            ORDER BY date_debut desc;

select * from v_zone_coupee
        where ref_zone like '%%' AND date_debut >= '0000-01-01' AND date_debut <= '9999-12-31'
        ORDER BY date_debut desc
        LIMIT 5 OFFSET 0;


SELECT * FROM (
    SELECT 
        niveau,
        SEC_TO_TIME(SUM(TIME_TO_SEC(duree_coupure))) AS heure_total_coupure,
        COUNT(id_coupure) AS frequence_coupure,
        (SELECT 
                heure_coupure
                FROM v_coupure_zone AS vcniv_sub
                WHERE vcniv_sub.niveau = vcniv.niveau
                GROUP BY heure_coupure
                ORDER BY COUNT(*) DESC
                LIMIT 1) AS heure_coupure_frequent,
        SEC_TO_TIME(AVG(TIME_TO_SEC(duree_coupure))) AS duree_moyenne_coupure
    FROM v_coupure_zone vcniv
    WHERE TRUE
    GROUP BY niveau 
    ORDER BY niveau 
) sr
WHERE niveau LIKE '%%';