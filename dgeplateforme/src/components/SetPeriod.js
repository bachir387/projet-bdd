// src/components/SetPeriod.js
import React, { useState, useEffect } from 'react';
import Navbar from './Navbar';
import axios from 'axios';

function SetPeriod() {
  const [dateDebut, setDateDebut] = useState('');
  const [dateFin, setDateFin] = useState('');
  const [message, setMessage] = useState('');
  const [currentPeriod, setCurrentPeriod] = useState(null);

  // Fonction pour récupérer la période de parrainage actuelle depuis l'API
  const fetchCurrentPeriod = async () => {
    try {
      const token = localStorage.getItem('token');
      const response = await axios.get('http://localhost:8000/parrainages/get_parrainage_period.php', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      // On suppose que l'API renvoie un objet contenant au moins "date_debut" et "date_fin"
      if (response.data && response.data.id) {
        setCurrentPeriod(response.data);
      } else {
        setCurrentPeriod(null);
      }
    } catch (err) {
      console.error(err);
      setMessage('Erreur lors de la récupération de la période de parrainage.');
    }
  };

  // Récupérer la période actuelle lors du montage du composant
  useEffect(() => {
    fetchCurrentPeriod();
  }, []);

  // Fonction pour définir une nouvelle période
  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage('');
    try {
      const token = localStorage.getItem('token');
      const response = await axios.post(
        'http://localhost:8000/parrainages/set_parrainage_period.php',
        { date_debut: dateDebut, date_fin: dateFin },
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
        }
      );
      setMessage(response.data.message);
      // Actualiser la période affichée
      fetchCurrentPeriod();
    } catch (err) {
      console.error(err);
      setMessage('Erreur lors de la définition de la période.');
    }
  };

  // Fonction pour supprimer la période actuelle
  const handleDelete = async () => {
    setMessage('');
    try {
      const token = localStorage.getItem('token');
      const response = await axios.delete('http://localhost:8000/parrainages/delete_parrainage_period.php', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      setMessage(response.data.message);
      // Actualiser la période affichée
      fetchCurrentPeriod();
    } catch (err) {
      console.error(err);
      setMessage("Erreur lors de la suppression de la période.");
    }
  };

  return (
    <div>
      <Navbar />
      <div className="container">
      <h2>Définir une Période de Parrainage</h2>
      {message && <p>{message}</p>}
      <form onSubmit={handleSubmit}>
        <div>
          <label>Date Début: </label>
          <input
            type="date"
            value={dateDebut}
            onChange={(e) => setDateDebut(e.target.value)}
            required
          />
        </div>
        <div>
          <label>Date Fin: </label>
          <input
            type="date"
            value={dateFin}
            onChange={(e) => setDateFin(e.target.value)}
            required
          />
        </div>
        <button type="submit">Définir la Période</button>
      </form>
      <hr />
      <h3>Période Actuelle</h3>
      {currentPeriod ? (
        <div>
          <p>
            <strong>Date Début:</strong> {currentPeriod.date_debut} <br />
            <strong>Date Fin:</strong> {currentPeriod.date_fin}
          </p>
          <button onClick={handleDelete}>Supprimer la Période</button>
        </div>
      ) : (
        <p>Aucune période de parrainage définie.</p>
      )}
      </div>
    </div>
  );
}

export default SetPeriod;
