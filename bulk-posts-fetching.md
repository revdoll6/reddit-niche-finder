# Bulk Posts Fetching for Saved Audiences

## Overview

This document describes a feature enhancement that will automatically fetch up to 500 posts for each subreddit when a user creates a new audience. Post fetching will happen in the background immediately after audience creation.

## Current Functionality

The existing audience creation workflow allows users to:
- Search for subreddits
- Select multiple subreddits
- Save them as an audience with a name and description

Currently, the application only stores basic subreddit metadata without the actual post content.

## Enhancement Goal

Add automatic post fetching to the audience creation process:

1. When a user saves a new audience:
   - The audience will be saved to the database as normal
   - A background process will automatically start fetching up to 500 recent posts for each subreddit
   - These posts will be stored for later analysis

2. User experience:
   - The user will be notified that post fetching has begun
   - The user can continue using the application while posts are being fetched
   - A status indicator will show the progress of the fetching process

## Technical Approach

At a high level, the implementation will:

1. Add a posts storage table in the database (`audience_subreddit_posts`)
2. Create a background job system to fetch posts without blocking the UI (`FetchSubredditPostsJob`)
3. Fetch posts in batches using Reddit's API pagination
4. Properly handle API rate limits and potential failures
5. Track and display status to users through a modal component

## Implementation Details

### Database Structure

The `audience_subreddit_posts` table includes:
- `audience_id` - Links to the parent audience
- `subreddit_name` - The name of the subreddit
- `posts_data` - JSON column containing all fetched posts
- `fetched_at` - Timestamp of when posts were last fetched
- `newest_post_id` - ID of the newest post (for future incremental fetching)
- `fetch_status` - Current status of the fetch operation (pending, in_progress, completed, failed)

### Job Processing

The posts are fetched through a background job:
- `FetchSubredditPostsJob` runs asynchronously using Laravel's queue system
- Jobs are dispatched to a dedicated 'posts' queue to avoid blocking other operations
- Each job fetches up to 500 posts for a single subreddit
- Posts are fetched in batches of 100 to respect Reddit's API limitations
- Job includes retry logic in case of temporary failures

### User Interface

A status modal shows the user the current progress:
- `AudienceStatusModal.vue` displays after audience creation
- Shows the status of each subreddit (pending, in_progress, completed, failed)
- Auto-refreshes every 10 seconds to show updated status
- Provides a manual refresh button and option to continue using the app
- Once all posts are fetched (or if fetching fails), the user is informed

### Data Maintenance

To keep the database size manageable:
- `CleanupOldPosts` command removes post data for audiences not viewed in the last 30 days
- This command runs daily via the Laravel scheduler
- Users can customize the retention period via a command option

## Benefits

This enhancement provides:
- Richer data for audience analysis
- Access to actual post content for deeper insights
- Automatic data collection without manual user action
- Improved user experience with background processing 
- Clear visibility into the data collection process

## Future Enhancements

Potential improvements for future versions:
1. Incremental post updates (only fetch new posts since last fetch)
2. Customizable fetch depth (let users choose how many posts to fetch)
3. More detailed post analytics based on the fetched content
4. Content filters to fetch only posts with specific characteristics 