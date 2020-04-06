import React, { useEffect, useState } from 'react';
import styles from './../Settings.css';
import Input from "../../Input/Input";

function TwitterSettings({ twitterSettings, forceSettings }) {
    const { token, token_secret, consumer_key, consumer_secret } = twitterSettings;
    const [twitterToken, setToken] = useState('');
    const [twitterSecret, setSecret] = useState('');
    const [ck, setCK] = useState('');
    const [cs, setCS] = useState('');
    const twitterSubmit = (e) => {
        e.preventDefault();
        const data = {
            'token': twitterToken,
            'secret': twitterSecret,
            'consumerKey': ck,
            'consumerSecret': cs,
        };
        fetch(window.ajaxURL + '?action=saveTwitterSettings', {
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
        setSecret(token_secret);
        setCK(consumer_key);
        setCS(consumer_secret);
    }, [twitterSettings]);
    return (
        <div className={styles.settings}>
            <form>
                <Input label={'Token'} value={twitterToken} onChange={setToken} type={'text'} />
                <Input label={'Token Secret'} value={twitterSecret} onChange={setSecret} type={'text'} />
                <Input label={'Consumer Key'} value={ck} onChange={setCK} type={'text'} />
                <Input label={'Consumer Secret'} value={cs} onChange={setCS} type={'text'} />
                <Input type={'submit'} click={ twitterSubmit } />
            </form>
        </div>
    )
}
export default TwitterSettings;