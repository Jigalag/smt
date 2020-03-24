import React from 'react';
import styles from './Title.css';

function Title({text}) {
    return (
        <div className={styles.title}>
            {
                text
            }
        </div>
    )
}
export default Title;