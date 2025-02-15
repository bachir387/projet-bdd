// src/App.js
import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import Login from './components/Login';
import Register from './components/Register';
import Dashboard from './components/Dashboard';
import UploadElectors from './components/UploadElectors';
import ValidateElectors from './components/ValidateElectors';
import AddCandidate from './components/AddCandidate';
import SetPeriod from './components/SetPeriod';
import CandidateList from './components/CandidateList';
import CandidateDetails from './components/CandidateDetails';

// Composant pour protéger les routes privées
const PrivateRoute = ({ children }) => {
  const token = localStorage.getItem('token');
  return token ? children : <Navigate to="/login" replace />;
};

function App() {
  return (
    <div>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route
          path="/"
          element={
            <PrivateRoute>
              <Dashboard />
            </PrivateRoute>
          }
        />
        <Route
          path="/upload-electors"
          element={
            <PrivateRoute>
              <UploadElectors />
            </PrivateRoute>
          }
        />
        <Route
          path="/validate-electors"
          element={
            <PrivateRoute>
              <ValidateElectors />
            </PrivateRoute>
          }
        />
        <Route
          path="/add-candidate"
          element={
            <PrivateRoute>
              <AddCandidate />
            </PrivateRoute>
          }
        />
        <Route
          path="/set-period"
          element={
            <PrivateRoute>
              <SetPeriod />
            </PrivateRoute>
          }
        />
        <Route
          path="/candidates"
          element={
            <PrivateRoute>
              <CandidateList />
            </PrivateRoute>
          }
        />
        <Route
          path="/candidates/:id"
          element={
            <PrivateRoute>
              <CandidateDetails />
            </PrivateRoute>
          }
        />
        {/* Redirection pour toutes les routes non reconnues */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </div>
  );
}

export default App;





// // src/App.js
// import React from 'react';
// import { Routes, Route, Navigate } from 'react-router-dom';
// import Login from './components/Login';
// import Dashboard from './components/Dashboard';
// import UploadElectors from './components/UploadElectors';
// import ValidateElectors from './components/ValidateElectors';
// import AddCandidate from './components/AddCandidate';
// import SetPeriod from './components/SetPeriod';


// // Composant pour protéger les routes privées
// const PrivateRoute = ({ children }) => {
//   const token = localStorage.getItem('token');
//   return token ? children : <Navigate to="/login" replace />;
// };

// function App() {
//   return (
//     <div>
//       <Routes>
//         <Route path="/login" element={<Login />} />
//         <Route 
//           path="/" 
//           element={
//             <PrivateRoute>
//               <Dashboard />
//             </PrivateRoute>
//           } 
//         />
//         <Route 
//           path="/upload-electors" 
//           element={
//             <PrivateRoute>
//               <UploadElectors />
//             </PrivateRoute>
//           } 
//         />
//         <Route 
//           path="/validate-electors" 
//           element={
//             <PrivateRoute>
//               <ValidateElectors />
//             </PrivateRoute>
//           } 
//         />
//         <Route 
//           path="/add-candidate" 
//           element={
//             <PrivateRoute>
//               <AddCandidate />
//             </PrivateRoute>
//           } 
//         />
//         <Route 
//           path="/set-period" 
//           element={
//             <PrivateRoute>
//               <SetPeriod />
//             </PrivateRoute>
//           } 
//         />
//         {/* Redirection pour toutes les routes non reconnues */}
//         <Route path="*" element={<Navigate to="/" replace />} />
//       </Routes>
//     </div>
//   );
// }

// export default App;
