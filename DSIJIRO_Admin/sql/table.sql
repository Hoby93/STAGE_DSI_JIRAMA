-- create database dsijiro2;

create table region(
    id_region int auto_increment primary key,
    libelle varchar(120)
);

create table district(
    id_district int auto_increment primary key,
    region_id int,
    libelle varchar(120),
    foreign key(region_id) references region(id_region)
);

create table commune(
    id_commune int auto_increment primary key,
    district_id int,
    libelle varchar(120),
    foreign key(district_id) references district(id_district)
);

create table fokontany(
    id_fokontany int auto_increment primary key,
    commune_id int,
    libelle varchar(120),
    foreign key(commune_id) references commune(id_commune)
);

create table lieu(
    id_lieu int auto_increment primary key,
    ref_osm varchar(120),
    secteur_id int,
    place varchar(120),
    nom varchar(255),
    foreign key(secteur_id) references secteur_electrique(id_secteur)
);

create table type_infrastructure(
    id_type int auto_increment primary key,
    libelle varchar(120),
    descr varchar(255)
);

CREATE TABLE infrastructure (
    id_infra INT AUTO_INCREMENT PRIMARY KEY,
    ref_infra VARCHAR(10) UNIQUE,
    type_infra INT,
    fkt_id int,
    libelle VARCHAR(120),
    adresse VARCHAR(120),
    contact VARCHAR(120),
    descr VARCHAR(255),
    horaire VARCHAR(255) DEFAULT 'lun:08:00-17:00;mar:08:00-17:00;mer:08:00-17:00;jeu:08:00-17:00;ven:08:00-17:00;sam:09:00-13:00;',
    coord POINT,
    FOREIGN KEY (type_infra) REFERENCES type_infrastructure(id_type),
    FOREIGN KEY (fkt_id) REFERENCES fokontany(id_fokontany)
);

create table secteur_electrique(
    id_secteur int auto_increment primary key,
    ref_secteur varchar(10) unique,
    region_id int,
    foreign key(region_id) references region(id_region)
);

create table post_electrique(
    id_post int auto_increment primary key,
    ref_post varchar(10) unique,
    secteur_id int,
    foreign key(secteur_id) references secteur_electrique(id_secteur)
);

create table zone_electrique(
    id_zone int auto_increment primary key,
    ref_zone varchar(120),
    ref_secteur varchar(120),
    specificite varchar(255),
    lieux varchar(255),
    postes varchar(255),
    degree int,
    coord POLYGON
);

create table zone_post(
    id_zone_fkt int auto_increment primary key,
    zone_id int,
    post_id int,
    foreign key(zone_id) references zone_electrique(id_zone),
    foreign key(post_id) references post_electrique(id_post)
);

create table zone_lieu(
    id_zone_lieu int auto_increment primary key,
    zone_id int,
    lieu_id int,
    foreign key(zone_id) references zone_electrique(id_zone),
    foreign key(lieu_id) references lieu(id_lieu)
);


create table confidentialite(
    id_confidentialite int auto_increment primary key,
    libelle varchar(120),
    valeur int
);

create table employe(
    id_employe int auto_increment primary key,
    nom varchar(120),
    prenom varchar(120),
    email varchar(80),
    mdp varchar(8),
    profil int
);

create table authorisation(
    id_authorisation int auto_increment primary key,
    secteur_id int, -- ? commune_id ? district_id ? region_id
    employe_id int,
    foreign key(secteur_id) references secteur_electrique(id_secteur),
    foreign key(employe_id) references employe(id_employe)
);

create table coupure(
    id_coupure int auto_increment primary key,
    ref_coupure varchar(10),
    confidentialite_id int,
    date_saisie datetime,
    date_annonce datetime,
    date_debut datetime,
    date_fin datetime,
    motif varchar(255),
    division varchar(255),
    sa varchar(255),
    employe_id int not null,
    etat int, -- est validee ou non
    foreign key(confidentialite_id) references confidentialite(id_confidentialite),
    foreign key(employe_id) references employe(id_employe)
);

create table coupure_zone(
    id_coupure_zone int auto_increment primary key,
    coupure_id int,
    zone_id int,
    foreign key(coupure_id) references coupure(id_coupure),
    foreign key(zone_id) references zone_electrique(id_zone)
);

create table contact(
    id_contact int auto_increment primary key,
    nom varchar(180),
    prenom varchar(255),
    fonction varchar(180),
    email varchar(180)
);

create table point_clee(
    id_point int auto_increment primary key,
    libelle varchar(255),
    descr varchar(255),
    degree int,
    coord POINT
);


INSERT INTO coupure_zone (coupure_id, zone_id)
    VALUES (null, null);