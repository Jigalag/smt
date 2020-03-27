import React, { useState, useEffect } from 'react';
import List from "../List/List";
const URL = 'https://api.twitter.com/1.1/statuses/user_timeline.json?tweet_mode=extended';

function Twitter({savedPostIds, isDisabledCheckbox, checkPost, forcePosts}) {
    const [tweets, setTweets] = useState([]);
    const [error, setError] = useState('');
    const [isError, setIsError] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    useEffect(() => {
        setTweets([]);
        const getTwitter = async () => {
            setIsLoading(true);
            const result = await fetch(window.ajaxURL + '?action=getTwitterFeeds');
            const content = await result.json();
            setTweets(content.data);
            setIsLoading(false);
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
    }, [forcePosts]);
    if (isLoading) {
        return (
            <div>
                Loading
            </div>
        )
    }
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