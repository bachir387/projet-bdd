// src/components/CandidateList.js
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import Navbar from './Navbar';

function CandidateList() {
  const [candidates, setCandidates] = useState([]);
  const [message, setMessage] = useState('');

  useEffect(() => {
    const fetchCandidates = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost:8000/candidats/list_candidates.php', {
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });
        setCandidates(response.data);
      } catch (error) {
        console.error(error);
        setMessage('Erreur lors de la récupération de la liste des candidats.');
      }
    };
    fetchCandidates();
  }, []);

  return (
    <div>
      <Navbar />
      <div className="container">
        <h2 className="section-title">Liste des Candidats</h2>
        {message && <p className="message error">{message}</p>}
        <div className="candidate-list">
          {candidates.map(candidate => (
            <div key={candidate.id} className="candidate-card">
              <h3>{candidate.nom} {candidate.prenom}</h3>
              <p>
                <strong>Parti Politique :</strong> {candidate.parti_politique || 'N/A'}
              </p>
              <Link to={`/candidates/${candidate.id}`} className="button-secondary">
                Voir les détails
              </Link>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

export default CandidateList;
