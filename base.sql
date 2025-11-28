CREATE TABLE Formulaire (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255),
    prenom VARCHAR(255),
    email VARCHAR(255),
    date_naissance DATE,
    lieu_naissance VARCHAR(255),
    Niv_Diplome VARCHAR(255),
    adresse VARCHAR(255),
    secu VARCHAR(15),
    telephone VARCHAR(255),
    cv_path VARCHAR(255)
);


CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    IsAdmin Boolean;
    IsRh Boolean;
);



