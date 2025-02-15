// src/components/ValidateElectors.js
import React, { useState } from 'react';
import Navbar from './Navbar';
import axios from 'axios';

function ValidateElectors() {
  const [message, setMessage] = useState('');

  const handleValidation = async () => {
    try {
      const token = localStorage.getItem('token');
      const response = await axios.post('http://localhost:8000/electeurs/validate_list.php', {}, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      setMessage(response.data.message);
    } catch (err) {
      setMessage('Validation failed.');
    }
  };

  return (
    <div>
      <Navbar />
      <h2>Validate Electors Import</h2>
      <button onClick={handleValidation}>Validate</button>
      {message && <p>{message}</p>}
    </div>
  );
}

export default ValidateElectors;
