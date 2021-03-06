import React from 'react';
import styles from './Title.css';

function Title({text, publishPosts, publishSuccess, publishError, disabledPublish}) {
    return (
        <div className={styles.title}>
            {
                text
            }
            <button disabled={disabledPublish} onClick={() => publishPosts()} className={styles.button}>Publish Posts</button>
            <div>

                {
                    publishError && (
                        <span className={styles.error}>{publishError}</span>
                    )
                }
                {
                    publishSuccess && (
                        <span className={styles.success}>{publishSuccess}</span>
                    )
                }
            </div>
        </div>
    )
}
export default Title;
