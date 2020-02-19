import React, { useEffect, useState } from 'react';
import styles from './App.css';
import Header from "../Header/Header";
import {
    SOCIAL_MEDIA,
} from "../../../constants";

function App() {
    const [isAuth, setAuth] = useState(false);
    const [authEmail, setEmail] = useState('');
    const [userType, setUserType] = useState('');
    useEffect(() => {
    }, []);
    return (
        <div>
            <Header />
            {
                SOCIAL_MEDIA.map((media) => (
                    <div>
                        { media }
                    </div>
                ))
            }
        </div>
    )
}
export default App;