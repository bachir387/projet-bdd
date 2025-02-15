// src/components/Dashboard.js
import React, { useState, useEffect } from 'react';
import axios from 'axios';
import Navbar from './Navbar';

function Dashboard() {
  const [stats, setStats] = useState(null);
  const [message, setMessage] = useState('');

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost:8000/electeurs/dashboard_stats.php', {
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });
        if (response.data.success) {
          setStats(response.data);
        } else {
          setMessage(response.data.message);
        }
      } catch (error) {
        console.error(error);
        setMessage("Erreur lors de la récupération des statistiques.");
      }
    };

    fetchStats();
  }, []);

  return (
    <div>
      <Navbar />
      <div className="container">
      <h2>Dashboard</h2>
      {message && <p>{message}</p>}
      {stats ? (
        <div>
          <p>
            <strong>Nombre total de parrains :</strong> {stats.total_parrains}
          </p>
          <p>
            <strong>Nombre total de candidats :</strong> {stats.total_candidates}
          </p>
          <h3>Parrainages par candidat :</h3>
          {stats.sponsorship_stats.length > 0 ? (
            <table border="1" cellPadding="8" style={{ borderCollapse: 'collapse' }}>
              <thead>
                <tr>
                  <th>Candidat</th>
                  <th>Nombre de parrainages</th>
                </tr>
              </thead>
              <tbody>
                {stats.sponsorship_stats.map((item) => (
                  <tr key={item.id}>
                    <td>{item.nom} {item.prenom}</td>
                    <td>{item.total_parrainages}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          ) : (
            <p>Aucun parrainage enregistré pour l'instant.</p>
          )}
        </div>
      ) : (
        <p>Chargement des statistiques...</p>
      )}
      </div>
    </div>
  );
}

export default Dashboard;
