// src/components/AddCandidate.js
import React, { useState } from 'react';
import axios from 'axios';
import Navbar from './Navbar';

function AddCandidate() {
  const [step, setStep] = useState(1); // Étape 1 : saisir le numéro d'électeur ; Étape 2 : compléter les infos
  const [numeroElecteur, setNumeroElecteur] = useState('');
  const [candidateDetails, setCandidateDetails] = useState(null);
  const [additionalData, setAdditionalData] = useState({
    email: '',
    telephone: '',
    parti_politique: '',
    slogan: '',
    photo: '',
    
    url: ''
  });
  const [message, setMessage] = useState('');

  // Gestion de la première étape : vérification du numéro d'électeur
  const handleStepOneSubmit = async (e) => {
    e.preventDefault();
    setMessage('');
    try {
      const token = localStorage.getItem('token');
      const response = await axios.post('http://localhost:8000/candidats/add_candidate.php', 
        { numero_electeur: numeroElecteur }, 
        {
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        }
      );
      // Si le candidat existe dans la liste des électeurs et qu'il faut compléter les infos
      if (!response.data.success && response.data.candidate_details) {
        setCandidateDetails(response.data.candidate_details);
        setStep(2);
      } else {
        setMessage(response.data.message);
      }
    } catch (error) {
      console.error(error);
      setMessage("Erreur lors de la vérification du numéro d'électeur.");
    }
  };

  // Gestion de la seconde étape : compléter et envoyer les informations complémentaires
  const handleStepTwoSubmit = async (e) => {
    e.preventDefault();
    setMessage('');
    try {
      const token = localStorage.getItem('token');
      const data = {
        numero_electeur: numeroElecteur,
        email: additionalData.email,
        telephone: additionalData.telephone,
        parti_politique: additionalData.parti_politique,
        slogan: additionalData.slogan,
        photo: additionalData.photo,
        
        url: additionalData.url
      };
      const response = await axios.post('http://localhost:8000/candidats/add_candidate.php', data, {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });
      setMessage(response.data.message);
      if (response.data.success) {
        // Réinitialiser le formulaire
        setStep(1);
        setNumeroElecteur('');
        setCandidateDetails(null);
        setAdditionalData({
          email: '',
          telephone: '',
          parti_politique: '',
          slogan: '',
          photo: '',
          
          url: ''
        });
      }
    } catch (error) {
      console.error(error);
      setMessage("Erreur lors de l'ajout du candidat.");
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setAdditionalData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  return (
    <div>
      <Navbar />
      <div className="container">
      <h2>Ajouter un Candidat</h2>
      {message && <p>{message}</p>}
      {step === 1 && (
        <form onSubmit={handleStepOneSubmit}>
          <div>
            <label>Numéro d'électeur :</label>
            <input 
              type="text"
              value={numeroElecteur}
              onChange={(e) => setNumeroElecteur(e.target.value)}
              required
            />
          </div>
          <button type="submit">Vérifier</button>
        </form>
      )}
      {step === 2 && candidateDetails && (
        <div>
          <h3>Informations de base du candidat</h3>
          <p><strong>Nom :</strong> {candidateDetails.nom}</p>
          <p><strong>Prénom :</strong> {candidateDetails.prenom}</p>
          <p><strong>Date de naissance :</strong> {candidateDetails.date_naissance}</p>
          <h3>Compléter les informations du candidat</h3>
          <form onSubmit={handleStepTwoSubmit}>
            <div>
              <label>Email :</label>
              <input 
                type="email"
                name="email"
                value={additionalData.email}
                onChange={handleInputChange}
                required
              />
            </div>
            <div>
              <label>Téléphone :</label>
              <input 
                type="text"
                name="telephone"
                value={additionalData.telephone}
                onChange={handleInputChange}
                required
              />
            </div>
            <div>
              <label>Parti Politique :</label>
              <input 
                type="text"
                name="parti_politique"
                value={additionalData.parti_politique}
                onChange={handleInputChange}
              />
            </div>
            <div>
              <label>Slogan :</label>
              <input 
                type="text"
                name="slogan"
                value={additionalData.slogan}
                onChange={handleInputChange}
              />
            </div>
            <div>
              <label>Photo (URL) :</label>
              <input 
                type="text"
                name="photo"
                value={additionalData.photo}
                onChange={handleInputChange}
              />
            </div>
            
           
            <div>
              <label>URL :</label>
              <input 
                type="text"
                name="url"
                value={additionalData.url}
                onChange={handleInputChange}
              />
            </div>
            <button type="submit">Ajouter le candidat</button>
          </form>
        </div>
      )}
      </div>
    </div>
  );
}

export default AddCandidate;




