# Audience Creation Guide - Reddit Niche Finder

This document provides a comprehensive overview of how audience creation works in the Reddit Niche Finder application, based on an in-depth analysis of the codebase.

## Table of Contents
1. [Database Schema](#database-schema)
2. [User Interface Components](#user-interface-components)
3. [Audience Creation Workflow](#audience-creation-workflow)
4. [Data Structure](#data-structure)
5. [API Endpoints](#api-endpoints)

## Database Schema

The application uses two main tables for audience management:

### `audiences` Table
```php
Schema::create('audiences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->text('description')->nullable();
    $table->timestamps();
});
```

This table stores the basic audience metadata:
- `id`: Primary key
- `user_id`: Foreign key to the users table (each audience belongs to a user)
- `name`: The name given to the audience
- `description`: Optional description of the audience
- `timestamps`: Created/updated timestamps

### `audience_subreddits` Table
```php
Schema::create('audience_subreddits', function (Blueprint $table) {
    $table->id();
    $table->foreignId('audience_id')->constrained()->onDelete('cascade');
    $table->string('subreddit_name');
    $table->json('subreddit_data');
    $table->timestamps();
    
    // Add unique constraint to prevent duplicate subreddits in same audience
    $table->unique(['audience_id', 'subreddit_name']);
});
```

This junction table stores the relationship between audiences and subreddits:
- `id`: Primary key
- `audience_id`: Foreign key to the audiences table
- `subreddit_name`: The name of the subreddit
- `subreddit_data`: A JSON field storing all the relevant metrics and data for the subreddit
- `unique constraint`: Prevents the same subreddit from being added to an audience multiple times

## User Interface Components

The audience creation process involves multiple UI components working together:

### 1. SavedAudiences.vue
This component displays all saved audiences on the dashboard and provides entry points to create new audiences.

Key features:
- Displays audience cards with metadata (name, total reach, subreddit count)
- "Create New Audience" button links to `/discovery/explorer`
- Each audience card has a "View Details" button
- Provides an empty state when no audiences exist
- Fetches audience data via API call to `/api/audiences`

### 2. AudienceBuilderPanel.vue
This fixed panel appears at the bottom of the screen during the subreddit discovery process, tracking which subreddits the user has selected.

Key features:
- Shows selected subreddits as chips/tags
- Provides a "Save as Audience" button
- Allows removing individual subreddits
- "Clear All" button to reset the selection

### 3. AudienceDetail.vue
This component serves two purposes:
- Viewing details of an existing audience
- Creating a new audience from selected subreddits

Key features:
- Tabs for different views: Overview, Combined Metrics, Content Analysis, Member Analysis
- When creating a new audience, shows a "Save Audience" button
- Displays a modal dialog for naming and describing the audience
- Shows detailed metrics for all included subreddits
- Allows removing subreddits from the audience

## Audience Creation Workflow

The audience creation process follows these steps:

1. **Discovery Phase**:
   - User navigates to the Explorer page (`/discovery/explorer`)
   - User searches for relevant subreddits
   - For each subreddit of interest, user clicks the "+" button to add it to their selection
   - Selected subreddits appear in the AudienceBuilderPanel at the bottom of the screen

2. **Save Phase**:
   - User clicks "Save as Audience" button in the AudienceBuilderPanel
   - This triggers the `saveAudience()` method which opens a modal dialog
   - User enters a name and optional description for the audience
   - User clicks "Save" which triggers `handleSaveAudience()`

3. **API Call**:
   - The application formats the selected subreddits with all their metadata
   - Makes a POST request to `/api/audiences` with the formatted data:
     ```javascript
     const response = await api.post('/api/audiences', {
       name: audienceName.value,
       description: audienceDescription.value,
       subreddits: subredditData
     });
     ```
   - On success, redirects to `/dashboard/saved-audiences`

4. **View and Manage**:
   - User can view all saved audiences on the dashboard
   - User can click "View Details" to see detailed metrics
   - User can delete audiences from the dashboard

## Data Structure

When saving an audience, the following data structure is used:

```javascript
{
  name: "Audience Name",
  description: "Audience Description",
  subreddits: [
    {
      name: "subreddit_name",
      data: {
        id: "subreddit_id",
        display_name: "subreddit_name",
        title: "Subreddit Title",
        icon_img: "icon_url",
        subscribers: 12345,
        active_user_count: 678,
        public_description: "Description text",
        created_utc: 1234567890,
        
        // Metrics
        engagement_rate: 4.5,
        growth_rate: 2.3,
        posts_per_day: 15.2,
        opportunity_score: 75.8,
        active_post_engagement: 8.7,
        keyword_engagement: 6.2,
        
        // Nested metrics for redundancy
        calculated_metrics: {
          engagement_rate: 4.5,
          growth_rate: 2.3,
          posts_per_day: 15.2,
          opportunity_score: 75.8,
          active_post_engagement: 8.7,
          keyword_engagement: 6.2
        }
      }
    },
    // More subreddits...
  ]
}
```

## API Endpoints

The audience feature utilizes these primary endpoints:

1. **GET `/api/audiences`**:
   - Retrieves all audiences belonging to the authenticated user
   - Used in the SavedAudiences.vue component

2. **POST `/api/audiences`**:
   - Creates a new audience
   - Requires name, description, and subreddits array
   - Used in AudienceDetail.vue's handleSaveAudience method

3. **GET `/api/audiences/{id}`**:
   - Retrieves a specific audience by ID
   - Used in AudienceDetail.vue when viewing an existing audience

4. **DELETE `/api/audiences/{id}`**:
   - Deletes a specific audience
   - Used in SavedAudiences.vue's deleteAudience method

5. **GET `/api/reddit/subreddits/{name}`**:
   - Gets detailed information about a specific subreddit
   - Used when building audiences from subreddit names

## Special Features

1. **Audience Metrics Calculation**:
   - The application calculates aggregate metrics for the entire audience:
     - Total Reach (sum of all subreddit subscribers)
     - Average Engagement (average engagement_rate across all subreddits)
     - Average Growth Rate (average growth_rate across all subreddits)

2. **Data Preservation**:
   - All subreddit metrics are stored in the `subreddit_data` JSON field
   - This ensures that audience metrics remain stable even if the source subreddit data changes

3. **Fallback Handling**:
   - The code includes robust fallback mechanisms for missing metrics
   - This ensures the UI doesn't break when metrics are unavailable

4. **Duplicate Prevention**:
   - The database schema prevents duplicate subreddits in the same audience
   - The UI allows removing subreddits from an audience before saving

This document provides a comprehensive overview of the audience creation workflow in the Reddit Niche Finder application, from UI interactions to database storage. 