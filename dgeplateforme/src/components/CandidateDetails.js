// src/components/CandidateDetails.js
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useParams } from 'react-router-dom';
import Navbar from './Navbar';

function CandidateDetails() {
  const { id } = useParams();
  const [candidate, setCandidate] = useState(null);
  const [newCode, setNewCode] = useState('');
  const [message, setMessage] = useState('');

  useEffect(() => {
    const fetchCandidate = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get(`http://localhost:8000/candidats/get_candidate.php?id=${id}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });
        setCandidate(response.data);
      } catch (error) {
        console.error(error);
        setMessage('Erreur lors de la récupération des détails du candidat.');
      }
    };
    fetchCandidate();
  }, [id]);

  const handleGenerateNewCode = async () => {
    try {
      const token = localStorage.getItem('token');
      const response = await axios.post(
        'http://localhost:8000/candidats/generate_code.php',
        { candidateId: id },
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
        }
      );
      setNewCode(response.data.newCode);
      setMessage('Nouveau code généré avec succès.');
    } catch (error) {
      console.error(error);
      setMessage("Erreur lors de la génération du nouveau code.");
    }
  };

  if (!candidate) {
    return (
      <div>
        <Navbar />
        <div className="container">
          <p>Chargement des détails du candidat...</p>
        </div>
      </div>
    );
  }

  return (
    <div>
      <Navbar />
      <div className="container candidate-details">
        <h2>Détails du candidat</h2>
        {message && <p className="message">{message}</p>}
        <div className="candidate-info">
          <div className="candidate-photo">
            {candidate.photo ? (
              <img src={candidate.photo} alt={candidate.nom} className="img-responsive" />
            ) : (
              <div className="no-photo">Aucune photo</div>
            )}
          </div>
          <div className="candidate-data">
            <p><strong>Nom :</strong> {candidate.nom}</p>
            <p><strong>Prénom :</strong> {candidate.prenom}</p>
            <p><strong>Date de naissance :</strong> {candidate.date_naissance}</p>
            
            <p><strong>Email :</strong> {candidate.email}</p>
            <p><strong>Téléphone :</strong> {candidate.telephone}</p>
            <p><strong>Parti Politique :</strong> {candidate.parti_politique || 'N/A'}</p>
            <p><strong>Slogan :</strong> {candidate.slogan || 'N/A'}</p>
           
            <p>
              <strong>URL :</strong>{' '}
              {candidate.url ? (
                <a href={candidate.url} target="_blank" rel="noreferrer">
                  {candidate.url}
                </a>
              ) : (
                'N/A'
              )}
            </p>
          </div>
        </div>
        {newCode && (
          <p className="new-code">
            <strong>Nouveau code :</strong> {newCode}
          </p>
        )}
        <button onClick={handleGenerateNewCode} className="btn btn-secondary">
          Générer un nouveau code d'authentification
        </button>
      </div>
    </div>
  );
}

export default CandidateDetails;
