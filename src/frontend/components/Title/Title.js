import React from 'react';
import styles from './Title.css';

function Title({text, publishPosts, publishSuccess, publishError}) {
    return (
        <div className={styles.title}>
            {
                text
            }
            <button onClick={() => publishPosts()} className={styles.button}>Publish Posts</button>
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