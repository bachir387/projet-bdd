// src/components/UploadElectors.js
import React, { useState } from 'react';
import Navbar from './Navbar';
import axios from 'axios';

function UploadElectors() {
  const [file, setFile] = useState(null);
  const [checksum, setChecksum] = useState('');
  const [message, setMessage] = useState('');
  const [uploadSuccessful, setUploadSuccessful] = useState(false);

  const handleFileChange = (e) => {
    setFile(e.target.files[0]);
  };

  // Gestion de l'upload du fichier CSV
  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage('');
    setUploadSuccessful(false);
    
    if (!file || !checksum) {
      setMessage('Veuillez sélectionner un fichier et saisir le checksum.');
      return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('checksum', checksum);

    try {
      const token = localStorage.getItem('token');
      const response = await axios.post('http://localhost:8000/electeurs/upload_list.php', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
          'Authorization': `Bearer ${token}`,
        },
      });
      setMessage(response.data.message);
      
      // Supposons que le backend renvoie un indicateur de succès (par exemple, response.data.success === true)
      if (response.data.success) {
        setUploadSuccessful(true);
      }
    } catch (err) {
      console.error(err);
      setMessage('Échec de l’upload.');
    }
  };

  // Gestion de la validation de l'importation
  const handleValidate = async () => {
    setMessage('');
    try {
      const token = localStorage.getItem('token');
      const response = await axios.post('http://localhost:8000/electeurs/validate_list.php', {}, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });
      setMessage(response.data.message);
      // Optionnel : réinitialiser l'état d'upload si nécessaire
      setUploadSuccessful(false);
    } catch (err) {
      console.error(err);
      setMessage("La validation a échoué.");
    }
  };

  return (
    <div>
      <Navbar />
      <div className="container">
      <h2>Upload Electors CSV</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <input type="file" onChange={handleFileChange} accept=".csv" required />
        </div>
        <div>
          <label>Checksum: </label>
          <input 
            type="text" 
            value={checksum} 
            onChange={(e) => setChecksum(e.target.value)} 
            required 
          />
        </div>
        <button type="submit">Upload</button>
      </form>
      {uploadSuccessful && (
        <div style={{ marginTop: '20px' }}>
          <button onClick={handleValidate}>Valider l'importation</button>
        </div>
      )}
      {message && <p>{message}</p>}
      </div>
    </div>
  );
}

export default UploadElectors;



// // src/components/UploadElectors.js
// import React, { useState } from 'react';
// import Navbar from './Navbar';
// import axios from 'axios';

// function UploadElectors() {
//   const [file, setFile] = useState(null);
//   const [checksum, setChecksum] = useState('');
//   const [message, setMessage] = useState('');

//   const handleFileChange = (e) => {
//     setFile(e.target.files[0]);
//   };

//   const handleSubmit = async (e) => {
//     e.preventDefault();
//     if (!file || !checksum) {
//       setMessage('Please select a file and enter the checksum.');
//       return;
//     }
//     const formData = new FormData();
//     formData.append('file', file);
//     formData.append('checksum', checksum);

//     try {
//       const token = localStorage.getItem('token');
//       const response = await axios.post('http://localhost:8000/electeurs/upload_list.php', formData, {
//         headers: {
//           'Content-Type': 'multipart/form-data',
//           'Authorization': `Bearer ${token}`,
//         },
//       });
//       setMessage(response.data.message);
//     } catch (err) {
//       setMessage('Upload failed.');
//     }
//   };

//   return (
//     <div>
//       <Navbar />
//       <h2>Upload Electors CSV</h2>
//       <form onSubmit={handleSubmit}>
//         <div>
//           <input type="file" onChange={handleFileChange} accept=".csv" required />
//         </div>
//         <div>
//           <label>Checksum: </label>
//           <input 
//             type="text" 
//             value={checksum} 
//             onChange={(e) => setChecksum(e.target.value)} 
//             required 
//           />
//         </div>
//         <button type="submit">Upload</button>
//       </form>
//       {message && <p>{message}</p>}
//     </div>
//   );
// }

// export default UploadElectors;
