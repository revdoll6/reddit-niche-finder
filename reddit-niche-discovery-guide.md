# Reddit Niche Explorer: Advanced API Implementation Guide

## Introduction

This guide outlines a comprehensive approach to building a robust niche discovery module using Reddit's API. By leveraging multiple API endpoints in combination, we can create a more accurate, in-depth subreddit discovery system based on user input and AI-enhanced keyword analysis.

## Core API Endpoints for Niche Discovery

### 1. Initial Subreddit Search

```
GET /subreddits/search
```

**Parameters:**
- `q`: The user's keyword input
- `limit`: Number of results (max 100)
- `sort`: "relevance" (default), "activity", or "subscribers"
- `include_over_18`: Boolean to include NSFW subreddits

**Description:**  
This is our starting point for discovering relevant subreddits. However, this endpoint alone is insufficient as it primarily matches against subreddit names and descriptions, not the actual content within them.

**Example Request:**
```
GET https://oauth.reddit.com/subreddits/search?q=digital+fashion&limit=25&sort=relevance
```

### 2. Subreddit Autocomplete

```
GET /api/subreddit_autocomplete_v2
```

**Parameters:**
- `query`: The user's keyword input
- `include_profiles`: Boolean (set to false)
- `include_over_18`: Boolean
- `limit`: Number of results (max 25)

**Description:**  
This endpoint provides faster results and sometimes catches subreddits that the main search misses. It's useful for expanding our initial pool of potential matches.

**Example Request:**
```
GET https://oauth.reddit.com/api/subreddit_autocomplete_v2?query=digital+fashion&include_profiles=false&limit=25
```

### 3. Search Posts Across Reddit

```
GET /search
```

**Parameters:**
- `q`: The user's keyword input
- `sort`: "relevance" (default), "hot", "new", "top", or "comments"
- `t`: Time filter ("hour", "day", "week", "month", "year", "all")
- `type`: "link" (posts), "sr" (subreddits), or "user"
- `limit`: Number of results (max 100)

**Description:**  
This is crucial for finding subreddits where the keyword is actively discussed, even if it's not in the subreddit name or description. By searching for posts containing the keyword, we can identify communities with relevant discussions.

**Example Request:**
```
GET https://oauth.reddit.com/search?q=digital+fashion&sort=relevance&t=year&type=link&limit=100
```

### 4. Subreddit Recommendation

```
GET /api/recommend/sr/{srnames}
```

**Parameters:**
- `srnames`: Comma-separated list of subreddit names (without r/ prefix)

**Description:**  
Once we have an initial set of relevant subreddits, we can use this endpoint to find similar communities. This helps expand our discovery beyond direct keyword matches.

**Example Request:**
```
GET https://oauth.reddit.com/api/recommend/sr/DigitalFashion,blender,augmentedreality
```

### 5. Get Subreddit Information

```
GET /r/{subreddit}/about
```

**Parameters:**
- `subreddit`: Subreddit name

**Description:**  
For each discovered subreddit, we need to fetch detailed information to assess its relevance, activity level, and other metrics.

**Example Request:**
```
GET https://oauth.reddit.com/r/DigitalFashion/about
```

### 6. Get Subreddit Posts

```
GET /r/{subreddit}/hot
GET /r/{subreddit}/new
GET /r/{subreddit}/top
```

**Parameters:**
- `limit`: Number of posts to retrieve (max 100)
- `t`: Time filter for top posts ("hour", "day", "week", "month", "year", "all")

**Description:**  
To analyze the actual content within a subreddit, we need to fetch recent posts. This allows us to verify keyword relevance and calculate engagement metrics.

**Example Request:**
```
GET https://oauth.reddit.com/r/DigitalFashion/top?t=month&limit=100
```

### 7. Get Post Comments

```
GET /r/{subreddit}/comments/{post_id}
```

**Parameters:**
- `depth`: Comment tree depth
- `limit`: Number of comments per level

**Description:**  
Comments often contain valuable information about how a community discusses the keyword topic. Analyzing comments improves our understanding of the subreddit's relevance.

**Example Request:**
```
GET https://oauth.reddit.com/r/DigitalFashion/comments/abc123?depth=1&limit=100
```

## Enhanced Niche Explorer Module Implementation

### User Flow Overview

The enhanced Niche Explorer module follows a guided, multi-step approach:

1. **Initial User Input**: Collect keywords and description, enhance with AI
2. **Keyword Processing**: Extract, group, and weight keywords semantically
3. **Subreddit Discovery**: Search based on weighted keywords
4. **Adaptive Analysis**: Optimize depth vs. breadth based on keyword count
5. **Result Presentation**: Display scored and ranked results

### Step 1: Initial User Input with AI Enhancement

1. **User provides description**: User writes a description of their niche interests
2. **AI enhancement**: System uses NLP/AI to enhance the description
3. **User approval**: User reviews and approves the enhanced description
4. **Keyword extraction**: System extracts up to 5 potential keywords from description

### Step 2: Semantic Keyword Grouping and Weighting

1. **Semantic grouping**: System groups similar keywords to avoid redundant searches
```javascript
   // Example function to identify similar keywords
function groupSimilarKeywords(keywords) {
  const groups = [];
     // Group keywords with semantic similarity > 0.85
     // For each group, select the most effective search term
     return groups; // Array of {primaryTerm, variants}
   }
   ```

2. **User selects up to 3 keywords**: User chooses keywords from different semantic groups

3. **Keyword weighting**: User assigns weights to the selected keywords
```javascript
   // Example weight assignments
   const weightedKeywords = [
     { term: "digital fashion", weight: 3 }, // Primary (weight 3)
     { term: "3d clothing", weight: 2 },     // Secondary (weight 2)
     { term: "metaverse", weight: 1 }        // Tertiary (weight 1)
   ];
   ```

### Step 3: Adaptive API Call Optimization Strategy

The number of keywords affects how API calls are allocated:

```javascript
// Calculate API budget based on keyword count
function calculateApiCallBudget(keywordCount) {
  const totalBudget = 100; // Reddit API limit per minute
  const reserveCalls = 5;   // Reserved for error handling
  
  // Essential initial calls
  const initialCalls = 3 * keywordCount; // 3 search methods per keyword
  const recommendationCalls = 1;
  
  // Calculate remaining budget for detailed analysis
  const remainingBudget = totalBudget - initialCalls - recommendationCalls - reserveCalls;
  
  // Cost per subreddit for detailed analysis (about + hot + top)
  const callsPerSubreddit = 3;
  
  // Maximum subreddits we can analyze with the remaining budget
  const maxSubreddits = Math.floor(remainingBudget / callsPerSubreddit);
  
  // Adjust depth vs. breadth based on keyword count
  let subredditsToAnalyze;
  let postsPerSubreddit;
  
  if (keywordCount === 1) {
    // Maximize breadth with 1 keyword
    subredditsToAnalyze = maxSubreddits;
    postsPerSubreddit = 8;
  } else if (keywordCount === 2) {
    // Balanced approach with 2 keywords
    subredditsToAnalyze = Math.min(maxSubreddits, 20);
    postsPerSubreddit = 10;
  } else {
    // Maximize depth with 3 keywords
    subredditsToAnalyze = Math.min(maxSubreddits, 15);
    postsPerSubreddit = 15;
  }
  
  return {
    initialCalls,
    recommendationCalls,
    subredditsToAnalyze,
    postsPerSubreddit,
    callsPerSubreddit,
    totalPlannedCalls: initialCalls + recommendationCalls + (subredditsToAnalyze * callsPerSubreddit),
    reserveCalls
  };
}
```

### Step 4: Implementation Scenarios by Keyword Count

#### Scenario 1: Single Keyword (Maximum Breadth)

With a single keyword, we maximize the number of subreddits analyzed:

```javascript
// API call budget with 1 keyword
const budget = {
  initialCalls: 3,         // 3 search endpoints for 1 keyword
  recommendationCalls: 1,  // 1 recommendation call
  subredditsToAnalyze: 30, // Analyze 30 subreddits in detail
  postsPerSubreddit: 8,    // 8 posts per subreddit for content analysis
  callsPerSubreddit: 3,    // 3 API calls per subreddit
  totalPlannedCalls: 94,   // 3 + 1 + (30 * 3) = 94
  reserveCalls: 6          // 6 reserve calls
};

// Example implementation flow:
// 1. Perform initial searches (0-2s, 3 calls)
// 2. Get recommendations (2-3s, 1 call)
// 3. Process 3 batches of 10 subreddits each (3-15s, 90 calls)
// 4. Display results (15-16s)
```

#### Scenario 2: Two Keywords (Balanced Approach)

With two keywords, we balance breadth and depth:

```javascript
// API call budget with 2 keywords
const budget = {
  initialCalls: 6,         // 3 search endpoints for 2 keywords
  recommendationCalls: 1,  // 1 recommendation call
  subredditsToAnalyze: 20, // Analyze 20 subreddits in detail
  postsPerSubreddit: 10,   // 10 posts per subreddit
  callsPerSubreddit: 3,    // 3 API calls per subreddit
  totalPlannedCalls: 67,   // 6 + 1 + (20 * 3) = 67
  reserveCalls: 33         // 33 reserve calls
};

// Example implementation flow:
// 1. Perform initial searches for both keywords (0-3s, 6 calls)
// 2. Get recommendations (3-4s, 1 call)
// 3. Process 2 batches of 10 subreddits each (4-12s, 60 calls)
// 4. Use some reserve for comment analysis (12-14s, 10 calls)
// 5. Display results (14-15s)
```

#### Scenario 3: Three Keywords (Maximum Depth)

With three keywords, we focus on depth over breadth:

```javascript
// API call budget with 3 keywords
const budget = {
  initialCalls: 9,         // 3 search endpoints for 3 keywords
  recommendationCalls: 1,  // 1 recommendation call
  subredditsToAnalyze: 15, // Analyze 15 subreddits in detail
  postsPerSubreddit: 15,   // 15 posts per subreddit
  callsPerSubreddit: 3,    // 3 API calls per subreddit
  totalPlannedCalls: 55,   // 9 + 1 + (15 * 3) = 55
  reserveCalls: 45         // 45 reserve calls (many used for comment analysis)
};

// Example implementation flow:
// 1. Perform initial searches for all keywords (0-4s, 9 calls)
// 2. Get recommendations (4-5s, 1 call)
// 3. Process 15 subreddits with deeper analysis (5-12s, 45 calls)
// 4. Use reserve for extensive comment analysis (12-16s, 20 calls)
// 5. Perform additional analysis on high-match posts (16-18s, 15 calls)
// 6. Display results (18-20s)
```

### Step 5: Weighted Relevance Scoring

Implementing a scoring system that incorporates keyword weights:

```javascript
// Calculate weighted relevance score for a subreddit
function calculateWeightedRelevance(subreddit, posts, weightedKeywords) {
  let totalScore = 0;
  let totalWeight = 0;
  
  // Score each keyword based on its weight
  for (const keyword of weightedKeywords) {
    const term = keyword.term;
    const weight = keyword.weight;
    
    // Calculate individual keyword score
    const nameMatch = subreddit.name.toLowerCase().includes(term.toLowerCase()) ? 3 : 0;
    const descMatch = subreddit.description.toLowerCase().includes(term.toLowerCase()) ? 2 : 0;
    
    // Check post content for keyword matches
    let contentMatchCount = 0;
    for (const post of posts) {
      if (post.title.toLowerCase().includes(term.toLowerCase()) || 
          post.selftext.toLowerCase().includes(term.toLowerCase())) {
        contentMatchCount++;
      }
    }
    const contentMatchScore = posts.length > 0 ? 
      (contentMatchCount / posts.length) * 5 : 0;
    
    // Combined keyword score
    const keywordScore = nameMatch + descMatch + contentMatchScore;
    
    // Add weighted score to total
    totalScore += keywordScore * weight;
    totalWeight += weight;
  }
  
  // Normalize by total weight
  const weightedRelevanceScore = totalWeight > 0 ? 
    totalScore / totalWeight : 0;
  
  // Combine with other metrics
  const sizeFactor = Math.min(1, Math.log10(subreddit.subscribers || 1) / 7);
  const engagementFactor = calculateEngagementScore(subreddit, posts);
  const growthFactor = subreddit.growth_rate ? subreddit.growth_rate / 100 : 0;
  
  // Final score combines relevance with other factors
  const finalScore = (
    weightedRelevanceScore * 0.6 + 
    sizeFactor * 0.15 +
    engagementFactor * 0.15 +
    growthFactor * 0.1
  );
  
  return {
    finalScore,
    components: {
      relevance: weightedRelevanceScore,
      size: sizeFactor,
      engagement: engagementFactor,
      growth: growthFactor
    }
  };
}
```

### Implementation Flow Example

```javascript
async function discoverNiche(weightedKeywords) {
  // 1. Calculate API budget based on number of keywords
  const budget = calculateApiCallBudget(weightedKeywords.length);
  console.log(`API Budget: ${JSON.stringify(budget)}`);
  
  // 2. Perform initial discovery for each keyword
  const initialResults = [];
  for (const keyword of weightedKeywords) {
    const results = await performInitialDiscovery(keyword.term);
    initialResults.push({
      keyword: keyword.term,
      weight: keyword.weight,
      results
    });
  }
  
  // 3. Merge and deduplicate initial results
  const mergedCandidates = mergeAndDeduplicateResults(initialResults);
  
  // 4. Get top subreddits for recommendation expansion
  const topSubreddits = getTopSubreddits(mergedCandidates, 5);
  
  // 5. Get recommendations
  const recommendedSubreddits = await getRecommendations(topSubreddits);
  
  // 6. Combine candidates
  const allCandidates = [...mergedCandidates, ...recommendedSubreddits];
  
  // 7. Prioritize candidates
  const prioritizedCandidates = prioritizeCandidates(
    allCandidates, 
    weightedKeywords,
    budget.subredditsToAnalyze
  );
  
  // 8. Perform detailed analysis in batches
  const analyzedResults = [];
  const batches = createBatches(prioritizedCandidates, 10);
  
  for (const batch of batches) {
    if (analyzedResults.length >= budget.subredditsToAnalyze) break;
    
    const batchResults = await analyzeSubredditBatch(
      batch, 
      weightedKeywords,
      budget.postsPerSubreddit
    );
    
    analyzedResults.push(...batchResults);
  }
  
  // 9. Sort and finalize results
  const finalResults = analyzedResults
    .sort((a, b) => b.score - a.score)
    .map(result => ({
      name: result.name,
      display_name: result.display_name,
      subscribers: result.subscribers,
      active_users: result.active_users,
      description: result.public_description,
      posts_per_day: result.posts_per_day,
      engagement_rate: result.engagement_rate,
      growth_rate: result.growth_rate,
      relevance_score: result.relevance_score,
      opportunity_score: result.score
    }));
  
  return finalResults;
}
```

## Rate Limit Considerations

Reddit's API has strict rate limits:
- 100 requests per minute with OAuth authentication (not 60 as sometimes reported)
- 10 requests per minute without authentication
- 1,000 requests per 10 minutes total

To work within these constraints:
1. Implement request batching and prioritization
2. Use caching for frequently accessed data
3. Implement exponential backoff for rate limit errors
4. Consider storing results in a database for future queries

## Optimization Techniques

1. **Progressive Loading**
   - Return initial results quickly based on `/subreddits/search` and `/api/subreddit_autocomplete_v2`
   - Continue processing and analyzing content in the background
   - Update results as deeper analysis completes

2. **Caching Strategy**
   - Cache search results for common keywords
   - Cache subreddit metadata and post data with appropriate TTLs
   - Implement a refresh mechanism for cached data

3. **Parallel Processing**
   - Use asynchronous requests to fetch data from multiple endpoints simultaneously
   - Process subreddits in parallel batches

## Optimized Implementation Scenario: Maximizing Subreddit Analysis

With the 100 requests per minute limit, we can create a highly optimized implementation that adapts based on the number of keywords (1-3):

### API Call Budget Calculation
- Initial discovery: 3 API calls per keyword
- Recommendation expansion: 1 API call
- Detailed subreddit info: 1 call per subreddit
- Content sampling: 2 calls per subreddit (hot + top posts)
- Total per subreddit for deep analysis: 3 calls

### Optimized Implementation Timeline: 1 Keyword (30 Subreddits)

#### Phase 1: Initial Discovery (0-2 seconds, 3 API calls)
```javascript
// Execute these requests in parallel
const [subredditSearch, autocompleteResults, postSearch] = await Promise.all([
  // Basic subreddit search (1 call)
  api.get('/subreddits/search', { q: keyword, limit: 100 }),
  
  // Subreddit autocomplete for faster results (1 call)
  api.get('/api/subreddit_autocomplete_v2', { query: keyword, limit: 25 }),
  
  // Post search to find relevant discussions (1 call)
  api.get('/search', { q: keyword, type: 'link', limit: 100, sort: 'relevance' })
]);

// Extract and merge unique subreddits from all results
const candidateSubreddits = extractUniqueSubreddits([
  ...subredditSearch.data.children,
  ...autocompleteResults.data.children,
  ...extractSubredditsFromPosts(postSearch.data.children)
]);

// Display initial results immediately
displayInitialResults(candidateSubreddits);
```

#### Phase 2: Expansion & Prioritization (2-4 seconds, 1 API call)
```javascript
// Get top 5 most promising subreddits based on initial metrics
const topSubreddits = getTopSubreddits(candidateSubreddits, 5);
const subredditNames = topSubreddits.map(s => s.name).join(',');

// Get similar subreddits (1 call)
const recommendedSubreddits = await api.get(`/api/recommend/sr/${subredditNames}`);

// Merge and prioritize all candidates
const prioritizedSubreddits = prioritizeSubreddits([
  ...candidateSubreddits,
  ...recommendedSubreddits.data
]);

// Select top 30 subreddits for detailed analysis
const subredditsForAnalysis = prioritizedSubreddits.slice(0, 30);
```

#### Phase 3: Detailed Analysis (4-10 seconds, up to 90 API calls)
```javascript
// Create batches of 10 subreddits to process in parallel
const batches = createBatches(subredditsForAnalysis, 10);

// Process each batch
for (const batch of batches) {
  // Get detailed info for each subreddit in parallel (10 calls)
  const detailedInfoPromises = batch.map(sub => 
    api.get(`/r/${sub.name}/about`)
  );
  
  // Get content samples for each subreddit in parallel (20 calls)
  const contentPromises = batch.flatMap(sub => [
    api.get(`/r/${sub.name}/hot`, { limit: 10 }),
    api.get(`/r/${sub.name}/top`, { t: 'month', limit: 10 })
  ]);
  
  // Wait for all requests to complete
  const [detailedInfos, contentSamples] = await Promise.all([
    Promise.all(detailedInfoPromises),
    Promise.all(contentPromises)
  ]);
  
  // Process results and update UI for this batch
  processAndDisplayBatchResults(batch, detailedInfos, contentSamples);
  
  // Short delay between batches to prevent rate limit issues
  if (batches.indexOf(batch) < batches.length - 1) {
    await delay(200);
  }
}
```

### Optimized Implementation Timeline: 2 Keywords (20 Subreddits)

The process follows a similar pattern but adjusts for two keywords:

- Initial Discovery: 6 API calls (3 per keyword)
- Expansion: 1 API call
- Detailed Analysis: 60 API calls (20 subreddits × 3 calls)
- Comment Analysis: 10 API calls (reserved for top results)
- Total: 77 API calls

### Optimized Implementation Timeline: 3 Keywords (15 Subreddits)

For three keywords, we focus more on depth than breadth:

- Initial Discovery: 9 API calls (3 per keyword)
- Expansion: 1 API call
- Detailed Analysis: 45 API calls (15 subreddits × 3 calls)
- Comment Analysis: 20 API calls (more extensive comment analysis)
- Additional Post Details: 15 API calls (more post content analysis)
- Total: 90 API calls

### Performance Metrics Comparison with Maximum Data Extraction

| Scenario | Keywords | Subreddits | Posts/Request | Total Posts Analyzed | API Calls | Processing Time |
|----------|----------|------------|---------------|--------------------|-----------|----------------|
| Breadth  | 1        | 22         | 100           | 6,600              | 92        | ~15 seconds    |
| Balanced | 2        | 20         | 100           | 6,000              | 95        | ~16 seconds    |
| Depth    | 3        | 16         | 100           | 4,800              | 94        | ~18 seconds    |

## Detailed Breakdown of API Calls by Keyword Count

### 1. Single Keyword Scenario (Maximum Breadth - 22 Subreddits)

With a single keyword, the algorithm focuses on analyzing a larger number of subreddits (22) to provide breadth of discovery:

#### Total API Budget: 100 calls per minute
- **Initial Discovery Phase**: 3 API calls total
  - 1 call to `/subreddits/search` (retrieving 100 results)
  - 1 call to `/api/subreddit_autocomplete_v2` (retrieving 25 results)
  - 1 call to `/search` (retrieving 100 posts containing the keyword)

- **Recommendation Phase**: 1 API call
  - 1 call to `/api/recommend/sr/{srnames}` (to find similar subreddits)

- **Detailed Analysis Phase**: 88 API calls
  - For each of the 22 prioritized subreddits:
    - 1 call to `/r/{subreddit}/about` (22 calls total)
    - 1 call to `/r/{subreddit}/hot?limit=100` (22 calls total)
    - 1 call to `/r/{subreddit}/new?limit=100` (22 calls total)
    - 1 call to `/r/{subreddit}/top?limit=100` (22 calls total)

- **Reserve**: 8 API calls (kept available for error handling)

#### Data Volume Processed:
- **Total subreddits analyzed in detail**: 22
- **Total posts per subreddit**: 300 (100 hot + 100 new + 100 top)
- **Total posts analyzed**: 6,600
- **Processing time**: ~15 seconds

### 2. Two Keywords Scenario (Balanced Approach - 20 Subreddits)

With two keywords, the algorithm balances breadth and depth:

#### Total API Budget: 100 calls per minute
- **Initial Discovery Phase**: 6 API calls total
  - 3 API calls per keyword × 2 keywords:
    - 2 calls to `/subreddits/search` (1 per keyword)
    - 2 calls to `/api/subreddit_autocomplete_v2` (1 per keyword)
    - 2 calls to `/search` (1 per keyword)

- **Recommendation Phase**: 1 API call
  - 1 call to `/api/recommend/sr/{srnames}`

- **Detailed Analysis Phase**: 80 API calls
  - For each of the 20 prioritized subreddits:
    - 1 call to `/r/{subreddit}/about` (20 calls total)
    - 1 call to `/r/{subreddit}/hot?limit=100` (20 calls total)
    - 1 call to `/r/{subreddit}/new?limit=100` (20 calls total)
    - 1 call to `/r/{subreddit}/top?limit=100` (20 calls total)

- **Comment Analysis**: 8 API calls
  - Used for analyzing comments on most relevant posts
  - 1 call to `/r/{subreddit}/comments/{post_id}?limit=100` for 8 different posts

- **Reserve**: 5 API calls (minimal reserve)

#### Data Volume Processed:
- **Total subreddits analyzed in detail**: 20
- **Total posts per subreddit**: 300 (100 hot + 100 new + 100 top)
- **Total posts analyzed**: 6,000
- **Comments analyzed**: ~800 (from 8 posts)
- **Processing time**: ~16 seconds

### 3. Three Keywords Scenario (Maximum Depth - 16 Subreddits)

With three keywords, the algorithm focuses on in-depth analysis of fewer subreddits:

#### Total API Budget: 100 calls per minute
- **Initial Discovery Phase**: 9 API calls total
  - 3 API calls per keyword × 3 keywords:
    - 3 calls to `/subreddits/search` (1 per keyword)
    - 3 calls to `/api/subreddit_autocomplete_v2` (1 per keyword)
    - 3 calls to `/search` (1 per keyword)

- **Recommendation Phase**: 1 API call
  - 1 call to `/api/recommend/sr/{srnames}`

- **Detailed Analysis Phase**: 64 API calls
  - For each of the 16 prioritized subreddits:
    - 1 call to `/r/{subreddit}/about` (16 calls total)
    - 1 call to `/r/{subreddit}/hot?limit=100` (16 calls total)
    - 1 call to `/r/{subreddit}/new?limit=100` (16 calls total)
    - 1 call to `/r/{subreddit}/top?limit=100` (16 calls total)

- **Comment Analysis**: 20 API calls
  - Used for in-depth comment analysis on the most relevant posts
  - 1 call to `/r/{subreddit}/comments/{post_id}?limit=100` for 20 different posts
  - Allows for deeper understanding of community discussions

- **Reserve**: 6 API calls

#### Data Volume Processed:
- **Total subreddits analyzed in detail**: 16
- **Total posts per subreddit**: 300 (100 hot + 100 new + 100 top)
- **Total posts analyzed**: 4,800
- **Comments analyzed**: ~2,000 (from 20 posts)
- **Processing time**: ~18 seconds

### API Calls Distribution Visualization

```
Single Keyword (1):
┌───────────────┬─────────────────────────────────────────────────────┐
│ Phase         │ API Calls Distribution                              │
├───────────────┼─────────────────────────────────────────────────────┤
│ Initial       │ ███ 3 calls                                         │
│ Recommendation│ █ 1 call                                            │
│ Detailed      │ ████████████████████████████████████████████ 88 calls│
│ Reserved      │ ████ 8 calls                                        │
└───────────────┴─────────────────────────────────────────────────────┘

Two Keywords (2):
┌───────────────┬─────────────────────────────────────────────────────┐
│ Phase         │ API Calls Distribution                              │
├───────────────┼─────────────────────────────────────────────────────┤
│ Initial       │ ██████ 6 calls                                      │
│ Recommendation│ █ 1 call                                            │
│ Detailed      │ ████████████████████████████████████████ 80 calls   │
│ Comment       │ ████ 8 calls                                        │
│ Reserved      │ ███ 5 calls                                         │
└───────────────┴─────────────────────────────────────────────────────┘

Three Keywords (3):
┌───────────────┬─────────────────────────────────────────────────────┐
│ Phase         │ API Calls Distribution                              │
├───────────────┼─────────────────────────────────────────────────────┤
│ Initial       │ █████████ 9 calls                                   │
│ Recommendation│ █ 1 call                                            │
│ Detailed      │ ████████████████████████████████████ 64 calls       │
│ Comment       │ ██████████ 20 calls                                 │
│ Reserved      │ ███ 6 calls                                         │
└───────────────┴─────────────────────────────────────────────────────┘
```

### Key Advantages of Maximizing Data Extraction

1. **Complete Dataset**: By always requesting the maximum 100 posts per call, we get 3× more data than requesting only 30-35 posts per call, providing more accurate analysis with minimal additional API cost

2. **Comprehensive Analysis**: Including all three post types (hot, new, and top) ensures we capture:
   - Current trending content (hot)
   - Most recent activity (new)
   - All-time best content (top)

3. **Efficient Resource Allocation**: As keyword count increases, we shift from breadth to depth:
   - With 1 keyword: We analyze more subreddits (22) to discover a wide range of communities
   - With 3 keywords: We analyze fewer subreddits (16) but perform deeper analysis with extensive comment review

4. **Adaptive to Variable Search Results**: When search returns:
   - Less than 22 results: All subreddits are analyzed in detail
   - More than 22 results: Advanced prioritization algorithm selects the most promising candidates

The approach maintains flexibility to adjust the number of subreddits analyzed based on how many high-quality results the initial search returns, ensuring optimal use of available API calls regardless of search result volume.

## Rate Limit Considerations

Reddit's API has strict rate limits:
- 100 requests per minute with OAuth authentication (not 60 as sometimes reported)
- 10 requests per minute without authentication
- 1,000 requests per 10 minutes total

To work within these constraints:
1. Implement request batching and prioritization
2. Use caching for frequently accessed data
3. Implement exponential backoff for rate limit errors
4. Consider storing results in a database for future queries

## Optimization Techniques

1. **Progressive Loading**
   - Return initial results quickly based on `/subreddits/search` and `/api/subreddit_autocomplete_v2`
   - Continue processing and analyzing content in the background
   - Update results as deeper analysis completes

2. **Caching Strategy**
   - Cache search results for common keywords
   - Cache subreddit metadata and post data with appropriate TTLs
   - Implement a refresh mechanism for cached data

3. **Parallel Processing**
   - Use asynchronous requests to fetch data from multiple endpoints simultaneously
   - Process subreddits in parallel batches

## Conclusion

By combining multiple Reddit API endpoints and implementing a multi-phase analysis approach, we can create a much more accurate and comprehensive niche discovery system. This approach goes beyond simple metadata matching to analyze actual content and community engagement, providing users with truly relevant subreddit recommendations based on their keyword interests. 

## Enhanced Niche Explorer Implementation with User Input Flow

### Multi-Step User Flow

Our enhanced niche discovery module follows a guided, multi-step approach:

1. **Initial User Input**: User provides description and initial keywords
2. **AI Enhancement**: System enhances the description and extracts keywords
3. **Semantic Grouping & Selection**: User selects from semantically grouped keywords
4. **Keyword Weighting**: User assigns importance weights to selected keywords
5. **Adaptive Search**: System performs optimized search based on keyword count

### Step 1: User Input with AI Enhancement

```javascript
async function enhanceUserInput(userDescription) {
  // Send description to AI for enhancement
  const enhancedDescription = await aiService.enhanceDescription({
    text: userDescription,
    purpose: "niche_discovery",
    expansionLevel: "moderate"
  });
  
  // Present to user for approval
  const approvedDescription = await presentForUserApproval(enhancedDescription);
  
  // Extract potential keywords
  const extractedKeywords = await aiService.extractKeywords({
    text: approvedDescription,
    maxKeywords: 5,
    minConfidence: 0.7
  });
  
  return {
    originalDescription: userDescription,
    enhancedDescription: approvedDescription,
    extractedKeywords
  };
}
```

### Step 2: Semantic Keyword Grouping

```javascript
function groupKeywordsSemantically(keywords) {
  // Group semantically similar keywords
  const groups = [];
  const processed = new Set();
  
  for (const keyword of keywords) {
    if (processed.has(keyword)) continue;
    
    const group = [keyword];
    processed.add(keyword);
    
    // Find similar keywords
    for (const other of keywords) {
      if (processed.has(other)) continue;
      
      // Calculate semantic similarity (using word embeddings or similar technique)
      const similarity = calculateSimilarity(keyword, other);
      
      // If similarity exceeds threshold, consider them part of same group
      if (similarity > 0.85) {
        group.push(other);
        processed.add(other);
      }
    }
    
    // Select best representative for the group
    const primaryTerm = selectBestTerm(group);
    
    groups.push({
      primaryTerm,
      alternatives: group.filter(term => term !== primaryTerm)
    });
  }
  
  return groups;
}
```

### Step 3: Keyword Weighting System

```javascript
function assignKeywordWeights(selectedKeywords) {
  // Default weights: Primary (3), Secondary (2), Tertiary (1)
  const weightedKeywords = selectedKeywords.map((keyword, index) => {
    let defaultWeight;
    
    if (index === 0) defaultWeight = 3;      // Primary keyword
    else if (index === 1) defaultWeight = 2; // Secondary keyword
    else defaultWeight = 1;                  // Tertiary keyword
    
    return {
      term: keyword,
      weight: defaultWeight
    };
  });
  
  // Allow user to adjust weights
  return presentWeightsForUserAdjustment(weightedKeywords);
}
```

### Step 4: Adaptive API Budget Calculation

```javascript
function calculateAdaptiveAPIBudget(weightedKeywords) {
  const keywordCount = weightedKeywords.length;
  const totalBudget = 100; // Reddit API limit per minute
  
  // Essential API calls
  const initialCalls = keywordCount * 3; // 3 search methods per keyword
  const recommendationCall = 1;
  const reserveCalls = 5; // Reserve for error handling
  
  // Remaining budget for detailed analysis
  const remainingBudget = totalBudget - initialCalls - recommendationCall - reserveCalls;
  
  // Cost per subreddit (3 calls each: about + hot + top)
  const costPerSubreddit = 3;
  
  // Calculate maximum subreddits we can analyze
  const maxSubreddits = Math.floor(remainingBudget / costPerSubreddit);
  
  // Dynamic allocation based on keyword count
  let targetSubreddits;
  let postsPerSubreddit;
  
  if (keywordCount === 1) {
    // With 1 keyword, maximize breadth (more subreddits)
    targetSubreddits = maxSubreddits;
    postsPerSubreddit = 8;
  } else if (keywordCount === 2) {
    // With 2 keywords, balanced approach
    targetSubreddits = Math.min(maxSubreddits, 20);
    postsPerSubreddit = 10;
  } else {
    // With 3 keywords, maximize depth (fewer subreddits, more posts)
    targetSubreddits = Math.min(maxSubreddits, 15);
    postsPerSubreddit = 15;
  }
  
  return {
    totalBudget,
    initialCalls,
    recommendationCall,
    targetSubreddits,
    postsPerSubreddit,
    costPerSubreddit,
    totalPlannedCalls: initialCalls + recommendationCall + (targetSubreddits * costPerSubreddit),
    remainingCalls: totalBudget - (initialCalls + recommendationCall + (targetSubreddits * costPerSubreddit))
  };
}
```

### Implementation Scenarios Based on Keyword Count

#### Scenario Comparison

| Scenario   | Keywords | Initial Calls | Subreddits | Posts/Subreddit | Total API Calls | Reserved |
|------------|----------|---------------|------------|-----------------|-----------------|----------|
| Breadth    | 1        | 3 + 1         | 30         | 8               | 94              | 6        |
| Balanced   | 2        | 6 + 1         | 20         | 10              | 67              | 33       |
| Depth      | 3        | 9 + 1         | 15         | 15              | 55              | 45       |

#### Single Keyword Example (Maximum Breadth)

When a user selects only one keyword, we maximize breadth by analyzing more subreddits (30):

```javascript
// Implementation for 1 keyword scenario:
async function singleKeywordImplementation(keyword) {
  // Initial discovery (3 API calls)
  const initialResults = await performParallelSearches(keyword);
  displayInitialResults(initialResults);
  
  // Get recommendations (1 API call)
  const recommendedSubreddits = await getRecommendations(initialResults);
  
  // Select up to 30 subreddits for detailed analysis
  const subredditsToAnalyze = prioritizeSubreddits(
    [...initialResults, ...recommendedSubreddits],
    30
  );
  
  // Analyze in batches of 10 subreddits (90 API calls)
  const analyzedResults = await processBatches(subredditsToAnalyze, 8);
  
  // Return results (no additional API calls)
  return finalizeResults(analyzedResults);
}
```

#### Three Keywords Example (Maximum Depth)

With three keywords, we focus on depth over breadth, analyzing fewer subreddits (15) but more posts per subreddit:

```javascript
// Implementation for 3 keywords scenario:
async function threeKeywordImplementation(weightedKeywords) {
  // Initial discovery for all keywords (9 API calls)
  const initialResults = [];
  for (const keyword of weightedKeywords) {
    const results = await performParallelSearches(keyword.term);
    initialResults.push({
      keyword: keyword.term,
      weight: keyword.weight,
      results
    });
  }
  
  // Merge and display initial results
  const mergedResults = mergeResultsByKeywordWeight(initialResults);
  displayInitialResults(mergedResults);
  
  // Get recommendations (1 API call)
  const recommendedSubreddits = await getRecommendations(mergedResults);
  
  // Select up to 15 subreddits for detailed analysis
  const subredditsToAnalyze = prioritizeSubreddits(
    [...mergedResults, ...recommendedSubreddits],
    15
  );
  
  // Analyze in batches (45 API calls)
  const analyzedResults = await processBatches(subredditsToAnalyze, 15);
  
  // Use remaining budget for in-depth comment analysis (20 API calls)
  const enhancedResults = await performCommentAnalysis(analyzedResults);
  
  // Additional post details for high-relevance posts (15 API calls)
  const finalResults = await enhanceWithAdditionalPostDetails(enhancedResults);
  
  // Return weighted results
  return finalizeResultsWithWeighting(finalResults, weightedKeywords);
}
```

### Weighted Relevance Calculation

The keyword weights influence how subreddits are scored:

```javascript
function calculateWeightedRelevance(subreddit, posts, weightedKeywords) {
  let totalScore = 0;
  let totalWeight = 0;
  
  // Calculate relevance for each keyword based on its weight
  for (const keyword of weightedKeywords) {
    // Check subreddit name and description
    const nameScore = subreddit.name.toLowerCase().includes(keyword.term.toLowerCase()) ? 3 : 0;
    const descScore = subreddit.description.toLowerCase().includes(keyword.term.toLowerCase()) ? 2 : 0;
    
    // Check post content
    let contentMatchCount = 0;
    for (const post of posts) {
      if (post.title.toLowerCase().includes(keyword.term.toLowerCase()) || 
          post.selftext.toLowerCase().includes(keyword.term.toLowerCase())) {
        contentMatchCount++;
      }
    }
    const contentScore = (contentMatchCount / Math.max(1, posts.length)) * 5;
    
    // Calculate keyword relevance score
    const keywordScore = nameScore + descScore + contentScore;
    
    // Apply weight
    totalScore += keywordScore * keyword.weight;
    totalWeight += keyword.weight;
  }
  
  // Normalize by total weight
  const weightedRelevance = totalScore / Math.max(1, totalWeight);
  
  // Combine with other metrics
  const subMetrics = calculateSubredditMetrics(subreddit, posts);
  
  // Final weighted score (relevance has highest importance)
  return (weightedRelevance * 0.6) + 
         (subMetrics.size * 0.1) + 
         (subMetrics.activity * 0.1) + 
         (subMetrics.engagement * 0.1) + 
         (subMetrics.growth * 0.1);
}
```

## Conclusion

By combining multiple Reddit API endpoints and implementing a multi-phase analysis approach, we can create a much more accurate and comprehensive niche discovery system. This approach goes beyond simple metadata matching to analyze actual content and community engagement, providing users with truly relevant subreddit recommendations based on their keyword interests. 