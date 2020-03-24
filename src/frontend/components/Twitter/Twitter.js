import React, { useState, useEffect } from 'react';
import List from "../List/List";
const URL = 'https://api.twitter.com/1.1/statuses/user_timeline.json?tweet_mode=extended';

function Twitter({savedPostIds, isDisabledCheckbox, checkPost}) {
    const [tweets, setTweets] = useState([]);
    const [error, setError] = useState('');
    const [isError, setIsError] = useState(false);
    useEffect(() => {
        const getTwitter = async () => {
            const result = await fetch(window.ajaxURL + '?action=getTwitterFeeds');
            const content = await result.json();
            setTweets(content.data);
            if (content.error) {
                setIsError(true);
                if (content.errorText) {
                    setError(content.errorText);
                }
                if (content.errors) {
                    let errorText = '';
                    content.errors.forEach((item) => {
                        errorText += item.message;
                        if (content.errors.length > 1) {
                            errorText += '; '
                        }
                    });
                    setError(errorText);
                }
            }
        };
        getTwitter();
    }, []);
    return (
        <div>
            {
                isError && (
                    <div>
                        {
                            error
                        }
                    </div>
                )
            }
            {
                !isError && (
                    <div>
                        <List listArray={tweets}
                              savedPostIds={savedPostIds}
                              checkPost={checkPost}
                              isDisabledCheckbox={isDisabledCheckbox}
                        />
                    </div>
                )
            }
        </div>
    )
}
export default Twitter;