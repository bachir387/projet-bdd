// src/components/Navbar.js
import React from 'react';
import { Link } from 'react-router-dom';

function Navbar() {
  const handleLogout = () => {
    localStorage.removeItem('token');
    window.location.href = '/login';
  };

  return (
    <nav>
      <ul style={{ display: 'flex', listStyle: 'none', gap: '1rem' }}>
        <li><Link to="/">Dashboard</Link></li>
        <li><Link to="/upload-electors">Upload Electors</Link></li>
        
        <li><Link to="/add-candidate">Add Candidate</Link></li>
        <li><Link to="/candidates">List Candidate</Link></li>
        <li><Link to="/set-period">Set Period</Link></li>
        <li><button onClick={handleLogout}>Logout</button></li>
      </ul>
    </nav>
  );
}

export default Navbar;


