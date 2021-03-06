<?php 
  
    class Database
    {

    	static $hostname = "localhost";
        static $dbname = "CSM";
        static $userdb = "root";
        static $mdpdb = "";
        static $driverdb = "mysql";

        static $conn;

        static function creation_Connexion()
        {
            if(empty(self::$conn))
            {
                self::$conn = new PDO(self::$driverdb.":host=".self::$hostname.";dbname=".self::$dbname, self::$userdb, self::$mdpdb);
            }
        }  

    	static function afficher_Fiches($archive = null)
    	{
    		self::creation_Connexion();
    		if($archive == null)
    		{
    			$sql = "SELECT `Titre`, `Code_ROM` FROM `fiches` WHERE `Archive` IS NULL";
    		}
            else
            {
            	$sql = "SELECT `Titre`,`Code_ROM` FROM `fiches` WHERE `Archive` = :archive";
            }
            $data = self::$conn->prepare($sql);
            $data->bindValue(":archive", $archive);
            $data->execute();
            return $data->fetchAll(PDO::FETCH_OBJ);
    	}

    	static function afficher_Info_Fiche($id)
    	{
    		self::creation_Connexion();

    		 $sql = "SELECT * FROM `fiches` INNER JOIN fiche_competence WHERE Fiches.Id_Fiche = :id ";
            $data = self::$conn->prepare($sql);
            $data->bindValue(":id", $id);
            $data->execute();
            $fiche = $data->fetch(PDO::FETCH_OBJ);
            $fiche->competences = self::getCompetence($fiche->Id_Fiche);
            return $fiche;
    	}

    	static function creation_Fiche($fiche)
    	{
    		self::creation_Connexion();

    		try 
    		{
    			$sql = "INSERT INTO `fiches` (`Id_Fiche`, `Titre`, `Code_ROM`, `Description_Courte`, `Description_Detaille`, `Photo`, `Fichier`, `Archive`) VALUES (:Id_Fiche, :Titre, :Code_ROM, :Description_Courte, :Description_Detaille, :Photo, :Fichier, :Archive)";

    			 $req = self::$conn->prepare($sql);
                
                $req->bindValue(":Id_Fiche", $fiche->Id_Fiche);
                $req->bindValue(":Titre", $fiche->Titre);
                $req->bindValue(":Code_ROM", $fiche->Code_ROM);
                $req->bindValue(":Description_Courte", $fiche->Description_Courte);
                $req->bindValue(":Description_Detaille", $fiche->Description_Detaille);
                $req->bindValue(":Photo", $fiche->Photo);
                $req->bindValue(":Fichier", $fiche->Fichier);
                $req->bindValue(":Archive", $fiche->Archive);

                $req->execute();
                
                $fiche->Id_Fiche = self::$conn->lastInsertId();
                self::creation_Competence($fiche);
               
                self::$conn->commit();
                return true;
    			
    		} 
    		catch (Exception $e) 
    		{
    			self::$conn->rollBack();
    			return false;
    		}
    	}

    	static function modification_Fiche($fiche)
    	{
    		self::creation_Connexion();
            try
            {
                self::$conn->beginTransaction();
                
                $sql = "UPDATE `fiches` SET `Id_Fiche` = :Id_Fiche, `Titre` = :Titre, `Code_ROM` = :Code_ROM, `Description_Courte` = :Description_Courte, `Description_Detaille` = :Description_Detaille, `Photo` = :Photo, `Fichier` = :Fichier, `Archive` = :Archive WHERE `fiches`.`Id_Fiche` = :Id_Fiche ";
                
                $req = self::$conn->prepare($sql);
                $req->bindValue(":Id_Fiche", $fiche->Id_Fiche);
                $req->bindValue(":Titre", $fiche->Titre);
                $req->bindValue(":Code_ROM", $fiche->Code_ROM);
                $req->bindValue(":Description_Courte", $fiche->Description_Courte);
                $req->bindValue(":Description_Detaille", $fiche->Description_Detaille);
                $req->bindValue(":Photo", $fiche->Photo);
                $req->bindValue(":Fichier", $fiche->Fichier);
                $req->bindValue(":Archive", $fiche->Archive);

                $req->execute();

                self::supression_Competence($fiche);

                self::creation_Competence($fiche);
                
                self::$conn->commit();
                return true;
            } 
            catch(PDOException $e)
            {
                self::$conn->rollBack();
                return false;
            }
        }

        static function getCompetence($id)
    	{
    		self::creation_Connexion();

    		$sql = "SELECT * FROM  `competences` INNER JOIN fiche_competence WHERE Fiches.Id_Fiche = :id ";
            $data = self::$conn->prepare($sql);
            $data->bindValue(":id", $id);
            $data->execute();
            return $data->fetch(PDO::FETCH_OBJ);
    	}

        static function creation_Competence($fiche)
        {
        	$sqlI = "INSERT INTO `fiche_competence` (`Id_Fiche`, `Id_Competence`) VALUES (:Id_Fiche, :Id_Competence)";
                $reqI = self::$conn->prepare($sqlI);

            foreach($fiche->competence as $competence){
                $reqI->bindValue(":Id_Fiche", $fiche->Id_Fiche);
                $reqI->bindValue(":Id_Competence", $p);
                $reqI->execute();
            }
        }

        static function supression_Competence($fiche)
        {
        	$sqlD = "DELETE FROM `fiche_competence` WHERE `fiches`.`Id_Fiche` = :Id_Fiche";
            $reqD = self::$conn->prepare($sqlD);
            $reqD->bindValue(":Id_Fiche", $fiche->Id_Fiche);
            $reqD->execute();
        }    	

    	static function afficher_Utilisateurs($archive = null)
    	{
    		self::creation_Connexion();
    		if($archive == null)
    		{
    			$sql = "SELECT `Nom`, `Prenom`, `Mail` FROM `utilisateurs` WHERE `Archive` IS NULL";
    		}
    		else
    		{
    			$sql = "SELECT `Nom`, `Prenom`, `Mail` FROM `utilisateurs` WHERE `Archive` = :archive";
    		}
            $data = self::$conn->prepare($sql);
            $data->bindValue(":archive", $archive);
            $data->execute();
            return $data->fetchAll(PDO::FETCH_OBJ);
    	}

    	static function afficher_Info_Utilisateur($id)
    	{
    		self::creation_Connexion();
    		$sql = "SELECT * FROM `utilisateurs` WHERE Id_Utilisateur = :id";
            $data = self::$conn->prepare($sql);
            $data->bindValue(":id", $id);
            $data->execute();
            return $data->fetchAll(PDO::FETCH_OBJ);
    	}

    	static function creation_Utilisateur($user)
    	{
    		self::creation_Connexion();
    		try 
    		{
    			$sql = "INSERT INTO `utilisateur` (`Id_Utilisateur`, `Nom`, `Prenom`, `Mail`, `Archive`, `Niveau`, `Mot_De_Passe`) VALUES (:Id_Utilisateur, :Nom, :Prenom, :Mail, :Archive, :Niveau, :Mot_De_Passe)";

    			 $req = self::$conn->prepare($sql);
                
                $req->bindValue(":Id_Utilisateur", $fiche->Id_Utilisateur);
                $req->bindValue(":Nom", $fiche->Nom);
                $req->bindValue(":Prenom", $fiche->Prenom);
                $req->bindValue(":Mail", $fiche->Mail);
                $req->bindValue(":Archive", $fiche->Archive);
                $req->bindValue(":Niveau", $fiche->Niveau);
                $req->bindValue(":Mot_De_Passe", $fiche->Mot_De_Passe);
                $req->execute();
                
                $fiche->Id_Utilisateur = self::$conn->lastInsertId();
                
                return true;	
    		} 
    		catch (Exception $e) 
    		{
    			return false;
    		}
    	}

    	static function modification_Utilisateur($user)
    	{
    		self::creation_Connexion();
    		try
            {                
                $sql = "UPDATE `utilisateur` SET `Id_Utilisateur` = :Id_Utilisateur, `Nom` = :Nom, `Prenom` = :Prenom, `Mail` = :Mail, `Archive` = :Archive, `Niveau` = :Niveau, `Mot_De_Passe` = :Mot_De_Passe WHERE `utilisateur`.`Id_Utilisateur` = :Id_Utilisateur ";
                
                $req = self::$conn->prepare($sql);
                $req->bindValue(":Id_Utilisateur", $fiche->Id_Utilisateur);
                $req->bindValue(":Nom", $fiche->Nom);
                $req->bindValue(":Prenom", $fiche->Prenom);
                $req->bindValue(":Mail", $fiche->Mail);
                $req->bindValue(":Archive", $fiche->Archive);
                $req->bindValue(":Niveau", $fiche->Niveau);
                $req->bindValue(":Mot_De_Passe", $fiche->Mot_De_Passe);

                $req->execute();
                return true;
            } 
            catch(PDOException $e)
            {
                return false;
            }

    	}
    }

   var_dump( Database::afficher_Utilisateurs());
   // var_dump(Database::afficher_Info_Utilisateur(1));