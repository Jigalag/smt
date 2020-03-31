import React, { useState, useEffect } from 'react';
import List from "../List/List";
const URL = 'https://graph.facebook.com/v6.0/lohikaSF/feed?fields=message,created_time,attachments{media},permalink_url';

function Facebook({savedPostIds, isDisabledCheckbox, checkPost, forcePosts}) {
    const [posts, setPosts] = useState([]);
    const [error, setError] = useState('');
    const [isError, setIsError] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    useEffect(() => {
        // setPosts([]);
        const getFacebook = async () => {
            setIsLoading(true);
            const result = await fetch(URL, {
                headers: {
                    Authorization: 'Bearer EAACFh7fqC2MBACSq5oihzpEuusEE3fx0FB9A8OHmZAjqJFbQUbVtllgZCcdDqTGGm0k0PhDwyznOqA9SNkkBZChl5OgAkKUNOIziTiVAKgRQ1fFionF3tXh85hfTxPFwtPHHCg42bkM99bcZBMpyxQ42SvGLROfYsCRm9Lyo7QZDZD'
                }
            });
            const content = await result.json();
            console.log(content);
            // setTweets(content.data);
            setIsLoading(false);
        };
        getFacebook();
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
                            // error
                        }
                    </div>
                )
            }
            {
                !isError && (
                    <div>
                        {/*<List listArray={tweets}*/}
                              {/*savedPostIds={savedPostIds}*/}
                              {/*checkPost={checkPost}*/}
                              {/*isDisabledCheckbox={isDisabledCheckbox}*/}
                        {/*/>*/}
                    </div>
                )
            }
        </div>
    )
}
export default Facebook;