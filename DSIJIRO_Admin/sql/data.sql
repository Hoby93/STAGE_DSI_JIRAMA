-- Insérer la région Analamanga
INSERT INTO region (id_region, libelle) VALUES 
    (1, 'Analamanga');

-- Insérer les districts pour Analamanga (region_id = 1)
INSERT INTO district (id_district, region_id, libelle) VALUES
    (1, 1, 'Antananarivo-Avaradrano'),
    (2, 1, 'Antananarivo-Atsimondrano'),
    (3, 1, 'Antananarivo-I');

-- Insérer les communes pour chaque district de la région Analamanga

-- Antananarivo-Avaradrano
INSERT INTO commune (id_commune, district_id, libelle) VALUES
    (1, 1, 'Sabotsy Namehana'),
    (2, 1, 'Ankadikey Ilafy'),
    (3, 1, 'Ambohimangakely');

-- Antananarivo-Atsimondrano
INSERT INTO commune (id_commune, district_id, libelle) VALUES
    (4, 2, 'Andoharanofotsy'),
    (5, 2, 'Ampanefy');

-- Antananarivo-I
INSERT INTO commune (id_commune, district_id, libelle) VALUES
    (6, 3, 'Commune Urbaine Antananarivo');

-- Insérer les fokontany pour chaque commune

INSERT INTO fokontany (id_fokontany, commune_id, libelle) VALUES
    -- Sab-Nam
    (1, 1, 'Tsarafara'),
    (2, 1, 'Namehana'),
    -- Ankadikely Ilafy
    (3, 2, 'Ilafy'),
    (4, 2, 'Manjaka'),
    (5, 2, 'Analamahitsy'),
    -- Ambohimangakely
    (6, 3, 'Ambohimangakely'),
    (7, 3, 'Andralanitra'),
    -- Andoram
    (8, 4, 'Tanjombato'),
    (9, 4, 'Malaza'),
    (10, 4, 'Morarano'),
    -- Ampanefy
    (11, 5, 'Ampanefy'),
    (12, 5, 'Antalata'),
    -- CUA
    (13, 6, 'Antaninandro'),
    (14, 6, 'Andavamamba'),
    (15, 6, '67ha'),
    (16, 6, 'Itaosy');


INSERT INTO type_infrastructure (libelle, descr) VALUES
    ('Shop', 'Boutiques pour les services de JIRAMA'),
    ('Agence', 'Agences pour le service client de JIRAMA');

INSERT INTO infrastructure (ref_infra, type_infra, fkt_id, libelle, adresse, contact, descr, coord) VALUES 
    ('SHP001', 1, 14, 'Shop | Anosizato', 'Anosizato AZ 324 Bis', 'example@contact.com', 'Boutiques pour les services de JIRAMA', ST_GeomFromText('POINT(-18.935820426085225 47.49901413917542)')),
    ('SHP002', 1, 16, 'Shop | Itaosy', 'Itaosy IT 324 Bis', 'example@contact.com', 'Boutiques pour les services de JIRAMA', ST_GeomFromText('POINT(-18.918384874226284 47.4684476852417)')),
    ('SHP003', 1, 15, 'Shop | 67Ha', '67Ha Sud 324 Bis', 'example@contact.com', 'Boutiques pour les services de JIRAMA', ST_GeomFromText('POINT(-18.903931676241474 47.50784397125245)')),
    ('SHP004', 1, 13, 'Shop | Mahamasina', 'Mahamasina MH 324 Bis', 'example@contact.com', 'Boutiques pour les services de JIRAMA', ST_GeomFromText('POINT(-18.90807288872151 47.53393650054932)')),
    ('SHP005', 1, 13, 'Shop | Tsimbazaza', 'Tsimbazaza AZ 324 Bis', 'example@contact.com', 'Boutiques pour les services de JIRAMA', ST_GeomFromText('POINT(-18.93178138566616 47.527670860290534)'));

INSERT INTO infrastructure (ref_infra, type_infra, fkt_id, libelle, adresse, contact, descr, coord) VALUES
    ('AG001', 2, 8, 'Agence | Ankadimbahoaka', 'Ankadimbahoaka AK 324 Bis', 'example@contact.com', 'Agences pour le service client de JIRAMA', ST_GeomFromText('POINT(-18.942010725577422 47.52359390258789)')),
    ('AG002', 2, 15, 'Agence | Anosy', 'Anosy AN 324 Bis', 'example@contact.com', 'Agences pour le service client de JIRAMA', ST_GeomFromText('POINT(-18.917491735283008 47.51852989196777)')),
    ('AG003', 2, 8, 'Agence | Soanierana', 'Soanierana 324 Bis', 'example@contact.com', 'Agences pour le service client de JIRAMA', ST_GeomFromText('POINT(-18.93409521976563 47.52204895019532)')),
    ('AG004', 2, 5, 'Agence | Ambodivona', 'Ambodivona AMB 324 Bis', 'example@contact.com', 'Agences pour le service client de JIRAMA', ST_GeomFromText('POINT(-18.8917510455047 47.52917289733887)')),
    ('AG005', 2, 16, 'Agence | Isotry', 'Isotry IS 324 Bis', 'example@contact.com', 'Agences pour le service client de JIRAMA', ST_GeomFromText('POINT(-18.909859262454216 47.51672744750977)'));


insert into secteur_electrique(id_secteur, ref_secteur) values
    (1, 'S1'),
    (2, 'S2');

insert into post_electrique(id_post, ref_post, secteur_id) values
    (1, 'P1', 1),
    (2, 'P2', 1),
    (3, 'P3', 2),
    (4, 'P4', 2);


insert into zone_electrique(id_zone, ref_zone, specificite, lieux, postes, degree, coord) values
    (1, 'Z1', 'hopital:2;prison:0', 'Andavamamba-67ha', 'P1-P2', 1, ST_GeomFromText('POLYGON((47.50041 -18.937089, 47.521526 -18.917441, 47.527192 -18.910783, 47.516204 -18.894543, 47.49526 -18.897141, 47.500754 -18.907373, 47.493028 -18.924424, 47.50041 -18.937089))')),
    (2, 'Z2', 'hopital:0;prison:0', 'Antaninandro', 'P3-P4', 0, ST_GeomFromText('POLYGON((47.516723 -18.893187, 47.52874 -18.90959, 47.537152 -18.913488, 47.547023 -18.911864, 47.545564 -18.896842, 47.529341 -18.901714, 47.516723 -18.893187))'));

insert into zone_post(zone_id, post_id) values
    (1, 1),
    (1, 2),
    (2, 3),
    (2, 4);

insert into zone_fkt(zone_id, fkt_id) values
    (1, 14),
    (1, 15),
    (2, 13);

insert into confidentialite(libelle, valeur) values
    ('Prive', 1),
    ('Publique', 0);

insert into employe values
    (1, 'John', 'Doe', 'john@example.com', '$2y$13$JBX0D9VGVv1qcvkqGyV2JeHZfVARNAEZfiTydiMP69pLc3mtYDOnq', 3),
    (2, 'Mary', 'Jane', 'marie@example.com', '$2y$13$JBX0D9VGVv1qcvkqGyV2JeHZfVARNAEZfiTydiMP69pLc3mtYDOnq', 2),
    (3, 'Sam', 'Smith', 'sam@example.com', '$2y$13$JBX0D9VGVv1qcvkqGyV2JeHZfVARNAEZfiTydiMP69pLc3mtYDOnq', 1);

insert into coupure values
    (1, 'CP001', 1,  '2024-07-29 08:00:00', '2024-07-29 08:00:00', '2024-07-30 06:00:00', '2024-07-30 09:00:00', 'Entretient pediodique sous coupure', null, null, 1, 1),
    (2, 'CP002', 1,  '2024-07-29 08:00:00', '2024-07-29 08:00:00', '2024-07-30 08:00:00', '2024-07-30 10:00:00', 'Remplacement supports', null, null, 1, 1);

insert into coupure_zone(coupure_id, zone_id) values
    (1, 1),
    (2, 2);


insert into contact(nom, prenom, fonction, email) values
    ('RAKOTONIRINA', 'Hobiniaina', null, 'rakotonirina@example.com'),
    ('RAHARISOA', 'Hanitra', null, 'hanitra@example.com'),
    ('RABEJEAN', 'Feno', null, 'rabejean@example.com'),
    ('ANDRIAMAMY', 'Fifaliana', null, 'andriamamy@example.com'),
    ('RAZAFIMANANTSOA', 'Fenitra', null, 'fenitra@example.com'),
    ('RAKOTOZAFY', 'Kevin', null, 'rakotozafy@example.com');