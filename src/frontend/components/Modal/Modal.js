import React from 'react';
import styles from './Modal.css';

function Modal({title, content, submit, cancel, openingTab}) {
    return (
        <div className={styles.modal}>
            <div className={styles.modalFade}/>
            <div className={styles.modalWrapper}>
                <div className={styles.close}/>
                <div className={styles.modalTitle}>
                    {title}
                </div>
                <div className={styles.modalContent}>
                    {content}
                </div>
                <div className={styles.modalButtons}>
                    <button onClick={() => submit()} className={styles.button}>
                        Save
                    </button>
                    <button onClick={() => cancel()} className={styles.button}>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    )
}
export default Modal;