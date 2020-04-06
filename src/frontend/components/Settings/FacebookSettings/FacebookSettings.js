import React, { useEffect, useState } from 'react';
import styles from './../Settings.css';
import Input from "../../Input/Input";

function FacebookSettings({ facebookSettings, forceSettings }) {
    const { token } = facebookSettings;
    const [facebookToken, setToken] = useState('');
    const facebookSubmit = (e) => {
        e.preventDefault();
        const data = {
            'token': facebookToken,
        };
        fetch(window.ajaxURL + '?action=saveFacebookSettings', {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            method: 'POST',
            body: JSON.stringify(data),
        }).then(() => {
            forceSettings()
        })
    };
    useEffect(() => {
        setToken(token);
    }, [facebookSettings]);
    return (
        <div className={styles.settings}>
            <form>
                <Input label={'Token'} value={facebookToken} onChange={setToken} type={'text'} />
                <Input type={'submit'} click={ facebookSubmit } />
            </form>
        </div>
    )
}
export default FacebookSettings;