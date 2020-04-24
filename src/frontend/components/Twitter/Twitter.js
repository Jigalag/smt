import React, { useState, useEffect } from 'react';
import List from "../List/List";

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
            const updatedContent = content.data.map((item) => {
                const permalink_url = `https://twitter.com/${item.user.screen_name}/status/${item.id_str}`;
                return {
                    entities: item.entities,
                    extended_entities: item.extended_entities,
                    full_text: item.full_text,
                    created_at: item.created_at,
                    id_str: item.id_str,
                    id: item.id,
                    permalink_url
                };
            });
            setTweets(updatedContent);
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
