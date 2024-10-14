create or replace view v_zone_coupee as
select 
    coupure_zone.id_coupure_zone,
    zone_electrique.*, 
    coupure.*
    from zone_electrique
    join coupure_zone on zone_electrique.id_zone = coupure_zone.zone_id
    join coupure on coupure.id_coupure = coupure_zone.coupure_id;


create view v_effectif_infra as
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

-- Avoir l'effectif 
create view v_effectif_sp as
SELECT 
    region.libelle AS region, 
    COUNT(DISTINCT secteur_electrique.ref_secteur) AS nb_secteur, 
    COUNT(DISTINCT post_electrique.ref_post) AS nb_post
FROM post_electrique
JOIN secteur_electrique ON post_electrique.secteur_id = secteur_electrique.id_secteur
JOIN region ON secteur_electrique.region_id = region.id_region
GROUP BY region.libelle;


CREATE VIEW v_effectif_rg AS
SELECT 
    v_infra.region,
    SUM(v_infra.nb_shop) AS nb_shop,
    SUM(v_infra.nb_agence) AS nb_agence,
    COALESCE(MAX(v_sp.nb_secteur), 0) AS nb_secteur, 
    COALESCE(MAX(v_sp.nb_post), 0) AS nb_post
FROM 
    v_effectif_infra v_infra
LEFT JOIN 
    v_effectif_sp v_sp 
ON 
    v_infra.region = v_sp.region
GROUP BY 
    v_infra.region;


create view v_coupure_post as
select 
    v_zone_coupee.id_coupure,
    v_zone_coupee.date_debut, 
    v_zone_coupee.date_fin, 
    post_electrique.ref_post as niveau, 
    secteur_electrique.ref_secteur, 
    CONCAT(DATE_FORMAT(date_debut, '%H:%i'), ' - ', DATE_FORMAT(date_fin, '%H:%i')) as heure_coupure,
    TIME_FORMAT(TIMEDIFF(v_zone_coupee.date_fin, v_zone_coupee.date_debut), '%H:%i') AS duree_coupure,
    DATE_FORMAT(v_zone_coupee.date_debut, '%Y-%m-%d') AS date_coupure
from v_zone_coupee
join zone_post on zone_post.zone_id = v_zone_coupee.id_zone
join post_electrique on zone_post.post_id = post_electrique.id_post
join secteur_electrique on secteur_electrique.id_secteur = post_electrique.secteur_id;


create view v_stat_coupure_post as
SELECT 
    ref_post as niveau,
    ref_secteur,
    region,
    SEC_TO_TIME(SUM(TIME_TO_SEC(duree_coupure))) AS heure_total_coupure,
    COUNT(id_coupure) AS frequence_coupure,
        (SELECT 
            heure_coupure
            FROM v_coupure_post AS vcp_sub
            WHERE vcp_sub.ref_post = vcp.ref_post
            AND vcp_sub.ref_secteur = vcp.ref_secteur
            AND vcp_sub.region = vcp.region
            GROUP BY heure_coupure
            ORDER BY COUNT(*) DESC
            LIMIT 1) AS heure_coupure_frequent,
    SEC_TO_TIME(AVG(TIME_TO_SEC(duree_coupure))) AS duree_moyenne_coupure
FROM 
    v_coupure_post vcp
GROUP BY 
    ref_post, ref_secteur, region
ORDER BY 
    ref_post;


create view v_coupure_secteur as
SELECT DISTINCT
    ref_secteur as niveau,
    id_coupure,
    date_debut,
    date_fin,
    date_coupure,
    heure_coupure,
    duree_coupure
FROM v_coupure_post;


CREATE VIEW v_stat_coupure_secteur AS
SELECT 
    niveau,
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
GROUP BY niveau
ORDER BY niveau;


create view v_coupure_zone as
select 
    id_coupure,
    ref_zone as niveau,
    lieux,
    date_debut,
    date_fin,
    DATE_FORMAT(date_debut, '%Y-%m-%d') AS date_coupure,
    CONCAT(DATE_FORMAT(date_debut, '%H:%i'), ' - ', DATE_FORMAT(date_fin, '%H:%i')) as heure_coupure,
    TIME_FORMAT(TIMEDIFF(date_fin, date_debut), '%H:%i') AS duree_coupure
from v_zone_coupee;


CREATE VIEW v_stat_coupure_zone AS
SELECT 
    ref_zone,
    max(lieux) as niveau ,
    SEC_TO_TIME(SUM(TIME_TO_SEC(duree_coupure))) AS heure_total_coupure,
    COUNT(id_coupure) AS frequence_coupure,
    (SELECT 
            heure_coupure
            FROM v_coupure_zone AS vcz_sub
            WHERE vcz_sub.ref_zone = vcz.ref_zone
            GROUP BY heure_coupure
            ORDER BY COUNT(*) DESC
            LIMIT 1) AS heure_coupure_frequent,
    SEC_TO_TIME(AVG(TIME_TO_SEC(duree_coupure))) AS duree_moyenne_coupure
FROM v_coupure_zone vcz
GROUP BY ref_zone
ORDER BY ref_zone; 



create view v_coupure_lieu as
select 
    v_zone_coupee.id_coupure,
    v_zone_coupee.date_debut, 
    v_zone_coupee.date_fin, 
    nom as niveau,
    DATE_FORMAT(date_debut, '%Y-%m-%d') AS date_coupure,
    CONCAT(DATE_FORMAT(date_debut, '%H:%i'), ' - ', DATE_FORMAT(date_fin, '%H:%i')) as heure_coupure,
    TIME_FORMAT(TIMEDIFF(v_zone_coupee.date_fin, v_zone_coupee.date_debut), '%H:%i') AS duree_coupure
from v_zone_coupee
join zone_lieu on zone_lieu.zone_id = v_zone_coupee.id_zone
join lieu on zone_lieu.lieu_id = lieu.id_lieu;


CREATE VIEW v_stat_coupure_lieu AS
SELECT 
    lieu as niveau,
    SEC_TO_TIME(SUM(TIME_TO_SEC(duree_coupure))) AS heure_total_coupure,
    COUNT(id_coupure) AS frequence_coupure,
    (SELECT 
            heure_coupure
            FROM v_coupure_lieu AS vcl_sub
            WHERE vcl_sub.lieu = vcl.lieu
            GROUP BY heure_coupure
            ORDER BY COUNT(*) DESC
            LIMIT 1) AS heure_coupure_frequent,
    SEC_TO_TIME(AVG(TIME_TO_SEC(duree_coupure))) AS duree_moyenne_coupure
FROM v_coupure_lieu vcl
GROUP BY lieu
ORDER BY lieu;